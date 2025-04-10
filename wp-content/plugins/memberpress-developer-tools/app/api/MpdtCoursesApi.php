<?php
if (!defined('ABSPATH')) {
  die('You are not allowed to call this page directly.');
}

use \memberpress\courses;

class MpdtCoursesApi extends MpdtBaseApi
{

  protected function register_more_routes()
  {
    register_rest_route($this->utils->namespace, '/' . $this->utils->base . '/settings', array(
      array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => array($this, 'get_settings'),
        'permission_callback' => array($this, 'rest_permissions_check'),
        'args'                => array()
      ),
    ));
    register_rest_route($this->utils->namespace, '/' . $this->utils->base . '/authors', array(
      array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => array($this, 'get_authors'),
        'permission_callback' => array($this, 'rest_permissions_check'),
        'args'                => array()
      ),
    ));
    register_rest_route($this->utils->namespace, '/' . $this->utils->base . '/(?P<id>[\d]+)/sections', array(
      array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => array($this, 'get_course_sections'),
        'permission_callback' => array($this, 'rest_permissions_check'),
        'args'                => array()
      ),
    ));
    register_rest_route($this->utils->namespace, '/' . $this->utils->base . '/(?P<id>[\d]+)/start', array(
      array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => array($this, 'mark_course_as_started'),
        'permission_callback' => array($this, 'rest_permissions_check'),
        'args'                => array()
      ),
    ));
    register_rest_route($this->utils->namespace, '/' . $this->utils->base . '/(?P<id>[\d]+)/complete', array(
      array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => array($this, 'mark_course_as_completed'),
        'permission_callback' => array($this, 'rest_permissions_check'),
        'args'                => array()
      ),
    ));
  }

  /**
   * Get all course settings
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function get_settings($request)
  {
    try {
      $settings = get_option('mpcs-options', []);

      if (!($settings)) {
        throw new Exception(__('There was a problem retrieving course settings.', 'memberpress-developer-tools'));
      }

      if (!class_exists(courses\helpers\App::class)) {
        throw new Exception(esc_html__('MemberPress Courses is not active.', 'memberpress-developer-tools'));
      }

      $settings['active_addons'] = [
        'quizzes' => courses\helpers\App::is_quizzes_addon_active(),
        'assignments' => courses\helpers\App::is_assignments_addon_active(),
        'gradebook' => courses\helpers\App::is_gradebook_addon_active(),
      ];
    } catch (Exception $e) {
      return new WP_Error('mepr_error', $e->getMessage(), array('status' => 404));
    }

    $response = rest_ensure_response($settings);

    if (is_wp_error($response)) {
      return $response;
    }

    $response->set_status(200);

    return $response;
  }

  /**
   * Get all course authors
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function get_authors($request)
  {
    global $wpdb;

    try {
      $this->check_dependency();

      // Query to get unique author details with first and last names
      $authors = $wpdb->get_results($wpdb->prepare("
          SELECT DISTINCT u.ID as author_id,
                 u.user_nicename as slug,
                 u.display_name as name,
                 fn.meta_value as first_name,
                 ln.meta_value as last_name
          FROM {$wpdb->posts} p
          INNER JOIN {$wpdb->users} u ON p.post_author = u.ID
          LEFT JOIN {$wpdb->usermeta} fn ON (u.ID = fn.user_id AND fn.meta_key = 'first_name')
          LEFT JOIN {$wpdb->usermeta} ln ON (u.ID = ln.user_id AND ln.meta_key = 'last_name')
          WHERE p.post_type = %s
          AND p.post_status = 'publish'
      ", courses\models\Course::$cpt));

      if (!($authors)) {
        throw new Exception(__('There was a problem retrieving course authors.', 'memberpress-developer-tools'));
      }

      $authors = array_map(function ($author) {
        return $author;
      }, $authors);
    } catch (Exception $e) {
      return new WP_Error('mepr_error', $e->getMessage(), array('status' => 404));
    }

    $response = rest_ensure_response($authors);

    if (is_wp_error($response)) {
      return $response;
    }

    $response->set_status(200);

    return $response;
  }

  /**
   * Get all course sections and lessons
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function get_course_sections($request)
  {
    $course_id = $request->get_param('id');
    $user_id = $request->get_param('user_id');

    if (!is_numeric($course_id)) {
      return new WP_Error('invalid_param', 'Invalid Course ID.', array('status' => 400));
    }

    try {
      $this->check_dependency();

      $course = new courses\models\Course($course_id);

      if (!($course->ID)) {
        throw new Exception(__('Course not found.', 'memberpress-developer-tools'));
      }
    } catch (Throwable $e) {  // Catches both Error and Exception
      return new WP_Error('mepr_error', $e->getMessage(), array('status' => 404));
    }

    $data = [];
    $sections = courses\models\Section::find_all_by_course($course_id);

    foreach ($sections as $section) {
      $lessons = $this->find_all_by_section($section->id, $user_id);

      $lessons = array_map(function ($lesson) use ($user_id) {
        $lesson_data = [
          'id' => $lesson->ID,
          'title' => $lesson->post_title,
          'type' => str_replace('mpcs-', '', $lesson->post_type)
        ];

        // Add progress if $user_id > 0
        if ($user_id > 0) {
          // $lesson_data['progress'] = sprintf('%d%%', $lesson->progress);
          $lesson_data['completed'] = courses\models\UserProgress::has_completed_lesson($user_id, $lesson->ID);
        }

        return $lesson_data;
      }, $lessons);

      $data['sections'][] = [
        'id' => $section->id,
        'title' => $section->title,
        'lessons' => $lessons
      ];
    }

    $response = rest_ensure_response($data);

    if (is_wp_error($response)) {
      return $response;
    }

    $response->set_status(200);
    return $response;
  }

  /**
   * Find all lessons by section
   *
   * @param int $section_id The section ID.
   * @param array $user_id The user ID.
   * @param array $post_types The post types to search for.
   * @param bool $include_private Whether to include private lessons.
   * @param bool $include_draft Whether to include draft lessons.
   * @return array
   */
  public static function find_all_by_section($section_id, $user_id = 0, $post_types = null, $include_private = true, $include_draft = null)
  {
    global $wpdb;
    $db = courses\lib\Db::fetch();

    static $section_lessons = array();

    $all_post_types = courses\models\Lesson::lesson_cpts(true);

    if ($post_types == null || ! is_array($post_types) || empty($post_types)) {
      $post_types = $all_post_types;
    } else {
      $post_types = array_filter($all_post_types, function ($ky) use ($post_types) {
        return in_array($ky, $post_types);
      }, ARRAY_FILTER_USE_KEY);
    }

    $post_types_string = implode("','", array_keys($post_types));

    $post_statuses = array('trash');
    if (false === $include_draft) {
      $post_statuses[] = 'draft';
    }
    if (!$include_private) {
      $post_statuses[] = 'private';
    }
    $post_statuses_string = implode("','", $post_statuses);

    $query = $wpdb->prepare(
      "
      SELECT p.ID, p.post_type, up.progress FROM {$wpdb->posts} AS p
        JOIN {$wpdb->postmeta} AS pm
          ON p.ID = pm.post_id
         AND pm.meta_key = %s
         AND pm.meta_value = %s
        JOIN {$wpdb->postmeta} AS pm_order
          ON p.ID = pm_order.post_id
         AND pm_order.meta_key = %s
        LEFT JOIN {$db->user_progress} AS up
          ON p.ID = up.lesson_id
         AND up.user_id = %d
       WHERE p.post_type in ('" . $post_types_string . "') AND p.post_status NOT IN ('" . $post_statuses_string . "')
       ORDER BY pm_order.meta_value * 1
       ",
      courses\models\Lesson::$section_id_str,
      $section_id,
      courses\models\Lesson::$lesson_order_str,
      $user_id
    );

    $key_query = md5($query);
    if (! isset($section_lessons[$key_query])) {
      $section_lessons[$key_query] = $wpdb->get_results($query);
    }

    $lessons = array();

    foreach ($section_lessons[$key_query] as $lesson) {
      if (array_key_exists($lesson->post_type, $post_types)) {
        $object = new $post_types[$lesson->post_type]($lesson->ID);
        $values = $object->get_values();
        if ($user_id) {
          $values['progress'] = $lesson->progress;
        }
        $lessons[] = (object) $values;
      }
    }

    return $lessons;
  }


  /**
   * Start a course
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function mark_course_as_started($request)
  {
    try {
      $this->check_dependency();
    } catch (Throwable $e) {
      return new WP_Error('mepr_error', $e->getMessage(), array('status' => 500));
    }

    $course_id = $request->get_param('id');
    $user_id = $request->get_param('user_id');

    if (is_user_logged_in() && !current_user_can('manage_options')) {
      $user_id = get_current_user_id();
    }

    if (!is_numeric($course_id)) {
      return new WP_Error('invalid_param', 'Invalid Course ID.', array('status' => 400));
    }

    if (!is_numeric($user_id)) {
      return new WP_Error('invalid_param', 'Invalid User ID.', array('status' => 400));
    }

    $course = new courses\models\Course($course_id);
    if (!$course->ID) {
      return new WP_Error('not_found', 'Course not found.', array('status' => 404));
    }

    $user = new MeprUser($user_id);
    if (!$user->ID) {
      return new WP_Error('not_found', 'User not found.', array('status' => 404));
    }

    $has_any_progress = courses\models\UserProgress::has_started_course($user_id, $course_id);
    if ($has_any_progress) {
      return new WP_Error('already_started', 'User has already started this course.', array('status' => 400));
    }

    $has_course_started_meta = get_user_meta($user_id, 'mpcs_course_started_' . $course_id, true);
    if ($has_course_started_meta) {
      return new WP_Error('already_started', 'User has already started this course.', array('status' => 400));
    }

    MeprEvent::record('mpca-course-started', $user, array('course_id' => $course_id));
    update_user_meta($user_id, 'mpcs_course_started_' . $course_id, MeprUtils::ts_to_mysql_date(time()));

    $response = rest_ensure_response(['success' => true]);

    if (is_wp_error($response)) {
      return $response;
    }

    $response->set_status(200);
    return $response;
  }

  /**
   * Complete a course
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function mark_course_as_completed($request)
  {
    try {
      $this->check_dependency();
    } catch (Throwable $e) {
      return new WP_Error('mepr_error', $e->getMessage(), array('status' => 500));
    }

    $course_id = $request->get_param('id');
    $user_id = $request->get_param('user_id');

    // if user is logged in, then the $user_id is their own id
    if (is_user_logged_in() && !current_user_can('manage_options')) {
      $user_id = get_current_user_id();
    }

    if (!is_numeric($course_id)) {
      return new WP_Error('invalid_param', 'Invalid Course ID.', array('status' => 400));
    }

    if (!is_numeric($user_id)) {
      return new WP_Error('invalid_param', 'Invalid User ID.', array('status' => 400));
    }

    $course = new courses\models\Course($course_id);

    if (!$course->ID) {
      return new WP_Error('not_found', 'Course not found.', array('status' => 404));
    }

    $user = new MeprUser($user_id);
    if (!$user->ID) {
      return new WP_Error('not_found', 'User not found.', array('status' => 404));
    }

    $has_completed_course = courses\models\UserProgress::has_completed_course($user_id, $course_id);
    if ($has_completed_course) {
      return new WP_Error('already_started', 'User has already completed this course.', array('status' => 400));
    }

    $lessons = (array) $course->lessons();
    foreach ($lessons as $lesson) {
      if (!courses\models\UserProgress::has_completed_lesson($user_id, $lesson->ID)) {
        $lesson->complete($user_id);
      }
    }

    $response = rest_ensure_response(['success' => true]);

    if (is_wp_error($response)) {
      return $response;
    }

    $response->set_status(200);
    return $response;
  }

  /**
   * Check if the dependency is active. If not, return an error
   *
   * @return void
   */
  protected function check_dependency()
  {
    if (!class_exists(courses\models\Course::class)) {
      throw new Exception(esc_html__('MemberPress Courses is not active.', 'memberpress-developer-tools'));
    }
  }
}
