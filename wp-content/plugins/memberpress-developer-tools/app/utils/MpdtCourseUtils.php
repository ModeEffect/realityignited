<?php if (!defined('ABSPATH')) {
  die('You are not allowed to call this page directly.');
}

use \memberpress\courses;
use \memberpress\quizzes;
use \memberpress\assignments;

class MpdtCourseUtils extends MpdtBaseCptUtils
{
  public $model_class = courses\models\Course::class;

  public function __construct()
  {
    $this->map  = array(
      'post_parent'           => false,
      'post_type'             => false,
      'post_password'         => false,
      'post_content_filtered' => false,
      'post_mime_type'        => false,
      'guid'                  => false,
    );

    parent::__construct();
  }

  /**
   * Used to implement custom search args
   *
   * @param Array $args
   * @return string
   */
  protected function get_data_query_custom_clauses(array $args)
  {
    global $wpdb;
    $clauses = '';

    // No custom clauses for single lesson
    if (isset($args['id'])) {
      return $clauses;
    }

    // Check if tax_relation is set and equals 'OR'
    $tax_relation = isset($args['tax_relation']) && $args['tax_relation'] === 'OR';

    // Prepare clauses for categories and tags
    $category_clause = $this->add_taxonomy_clauses('mpcs-course-categories', 'category', $args);
    $tag_clause = $this->add_taxonomy_clauses('mpcs-course-tags', 'tag', $args);

    if ($tax_relation) {
      if ($category_clause || $tag_clause) {
        $clauses .= " AND (";
        $clauses .= trim("$category_clause OR $tag_clause", ' OR '); // Remove trailing OR
        $clauses .= ")";
      }
    } else {
      if ($category_clause) {
        $clauses .= " AND $category_clause";
      }
      if ($tag_clause) {
        $clauses .= " AND $tag_clause";
      }
    }

    // Filter by include, include is an array of IDs
    if (isset($args['include']) && is_array($args['include'])) {
      $include = implode(',', array_map('absint', $args['include']));
      $clauses .= " AND p.ID IN ($include)";
    }

    // Filter by author
    if (isset($args['author']) && is_numeric($args['author'])) {
      $clauses .= $wpdb->prepare(
        "
            AND p.post_author = %d
            ",
        $args['author']
      );
    }

    // if mycourses arg is set to true
    if (isset($args['my_courses']) && $args['my_courses'] === 'true') {
      $user_id = isset($args['user_id']) ? absint($args['user_id']) : get_current_user_id();

      // if user is logged in, then the $user_id is their own id
      if (is_user_logged_in() && !current_user_can('manage_options')) {
        $user_id = get_current_user_id();
      }

      $course_ids = $this->get_user_course_ids($user_id);
      $course_ids = empty($course_ids) ? array(0) : $course_ids;
      $include = implode(',', array_map('absint', $course_ids));
      $clauses .= " AND p.ID IN ($include)";
    }

    // if logged-in user is not an admin,
    // hide courses where meta _mpcs_course_status is not set to enabled
    if (is_user_logged_in() && !current_user_can('manage_options') && !isset($args['my_courses'])) {
      $clauses .= " AND EXISTS (SELECT 1 FROM {$wpdb->postmeta} pm WHERE pm.post_id = p.ID AND pm.meta_key = '_mpcs_course_status' AND pm.meta_value = 'enabled')";
    }

    return $clauses;
  }

  /**
   * Add taxonomy clauses to the query
   *
   * @param string $taxonomy Taxonomy name
   * @param string $arg_key Argument key
   * @param array $args Arguments
   * @return string
   */
  protected function add_taxonomy_clauses($taxonomy, $arg_key, array $args)
  {
    global $wpdb;
    if (isset($args[$arg_key]) && is_string($args[$arg_key])) {
      $terms = array_map('trim', explode(',', $args[$arg_key]));

      $term_names = [];
      $term_ids = [];

      foreach ($terms as $term) {
        if (is_numeric($term)) {
          $term_ids[] = intval($term); // Ensure it's an integer
        } else {
          $term_names[] = $term;
        }
      }

      $name_placeholders = implode(', ', array_fill(0, count($term_names), '%s'));
      $id_placeholders = implode(', ', array_fill(0, count($term_ids), '%d'));

      $name_clause = !empty($term_names) ? "AND t.name IN ($name_placeholders)" : '';
      $id_clause = !empty($term_ids) ? "AND tt.term_id IN ($id_placeholders)" : '';

      return $wpdb->prepare(
        "
        EXISTS (
          SELECT 1
          FROM {$wpdb->term_relationships} tr
          INNER JOIN {$wpdb->term_taxonomy} tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
          INNER JOIN {$wpdb->terms} t ON (tt.term_id = t.term_id)
          WHERE tr.object_id = p.ID
          AND tt.taxonomy = %s
          $name_clause
          $id_clause
        )
        ",
        array_merge([$taxonomy], $term_names, $term_ids) // First, pass the taxonomy, then the term names and IDs
      );
    }
    return ''; // Return empty if no terms found
  }

  /**
   * Prepare the item for the REST response
   *
   * @param mixed $item WordPress representation of the item.
   * @param WP_REST_Request $request Request object.
   * @return mixed
   */
  public function trim_obj(array $course)
  {
    if (isset($course['emails'])) {
      unset($course['emails']);
    }

    if (isset($course['author']) && is_array($course['author'])) {
      if (isset($course['author']['email'])) {
        $author_image_url = get_avatar_url($course['author']['email']);
        $course['author']['avatar'] = $author_image_url;
        unset($course['author']['email']);
      }
    }

    return $course;
  }


  /**
   * Extend the object with additional data
   *
   * @param Array $object The object to extend.
   * @param Array $args Additional arguments.
   * @return Array
   */
  protected function extend_obj(array $object, array $args = array())
  {
    // Get the user's progress
    if (isset($args['user_id']) && is_numeric($args['user_id'])) {
      $user_id = absint($args['user_id']);
      $course = new courses\models\Course($object['id']);

      if (is_numeric($course->ID) && $course->ID > 0) {
        $score = absint($course->user_progress($user_id));
        $object['score'] = sprintf('%d%%', $score);
        $object['completed'] = $score == 100;
      }
    }

    // Course access status
    if (is_user_logged_in()) {
      $user = new MeprUser(get_current_user_id());
      $post = get_post($object['id']);

      $has_no_access = MeprRule::is_locked_for_user($user, $post);
      $object['locked'] = $has_no_access;

      if($has_no_access){
        $mepr_options = MeprOptions::fetch();
        $object['content'] = $mepr_options->unauthorized_message;
      }
    }

    // Single Course
    if (isset($args['id']) && is_numeric($args['id'])) {
      // Get categories and tags
      $categories = get_the_terms($args['id'], 'mpcs-course-categories');
      $tags = get_the_terms($args['id'], 'mpcs-course-tags');

      $category_data = array();
      $tag_data = array();

      // Check if categories are available and not an error
      if (! is_wp_error($categories) && ! empty($categories)) {
        foreach ($categories as $category) {
          $category_data[] = array(
            'id'   => $category->term_id,
            'name' => $category->name,
          );
        }
      }

      // Check if tags are available and not an error
      if (! is_wp_error($tags) && ! empty($tags)) {
        foreach ($tags as $tag) {
          $tag_data[] = array(
            'id'   => $tag->term_id,
            'name' => $tag->name,
          );
        }
      }

      $object['categories'] = $category_data;
      $object['tags'] = $tag_data;
    }

    // Get certificate URL
    if (isset($args['id'], $args['user_id']) && is_numeric($args['id']) && is_numeric($args['user_id'])) {
      if ('enabled' === $object['certificates_share_link']) {
        $cert_url = admin_url('admin-ajax.php?action=mpcs-course-certificate');
        $cert_url = add_query_arg(
          array(
            'user' => $args['user_id'],
            'course' => $args['id'],
            'shareable' => 'true'
          ),
          $cert_url
        );
        $object['certificate_url'] = esc_url($cert_url);
      }
    }

    // Add featured image
    $size = apply_filters('mpcs_course_thumbnail_size', 'mpcs-course-thumbnail');
    $object['thumbnail_url'] = get_the_post_thumbnail_url($object['id'], $size);

    // add permalink
    $object['url'] = get_permalink($object['id']);

    return $object;
  }

  /**
   * Get all course IDs for a user
   *
   * @return array
   */
  public function get_user_course_ids($user_id)
  {

    //Get Courses User has access too
    if (false == (get_transient('mpcs_mycourses_' . $user_id))) {
      $mepr_user = new MeprUser($user_id);
      if (empty($course_ids)) {
        $course_ids = array(0); //Empty arrays apply no filter on get_posts
      }

      // Remove courses the user does not have access to
      $courses = get_posts(array('post_type' => courses\models\Course::$cpt, 'posts_per_page' => -1));
      $allowed_courses = array_filter($courses, function ($course) use ($mepr_user) {
        return false == MeprRule::is_locked_for_user($mepr_user, $course);
      });

      if (isset($allowed_courses)) {
        $course_ids = array_column($allowed_courses, 'ID');
      }

      set_transient('mpcs_mycourses_' . $user_id, $course_ids, 24 * HOUR_IN_SECONDS);
      $transients[] = 'mpcs_mycourses_' . $user_id;
      update_option('mpcs_mycourses_', $transients);
    } else {
      $course_ids = get_transient('mpcs_mycourses_' . $user_id);
    }

    return $course_ids;
  }
}
