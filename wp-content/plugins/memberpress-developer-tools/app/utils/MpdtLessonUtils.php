<?php if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}
use \memberpress\courses;

class MpdtLessonUtils extends MpdtBaseCptUtils {
  public $model_class = courses\models\Lesson::class;

  public function __construct() {
    $this->map  = array(
      'post_parent'           => false,
      'post_type'             => false,
      'post_password'         => false,
      'post_content_filtered' => false,
      'post_mime_type'        => false,
      'guid'                  => false,
      'modified_gmt'          => false,
      'post_modified_gmt'     => false,
      'post_modified'         => false,
    );

    parent::__construct();
  }

  protected function get_data_query(Array $args, $count=false) {
    global $wpdb;
    $coursedb = courses\lib\Db::fetch();

    $rc = new ReflectionClass($this->model_class);

    try {
      $cpt = $rc->getStaticPropertyValue('cpt');
      $post_type_clause = $wpdb->prepare("
        AND p.post_type=%s
      ", $cpt);
    }
    catch(ReflectionException $e) {
      // That property must not exist so let's just blank it out
      $post_type_clause = '';
    }

    $id_clause='';
    if(!empty($args['id'])) {
      $id_clause = $wpdb->prepare("
         AND p.ID = %d
      ",
      $args['id']);
    }

    // Since most data we'll return is CPT related the default
    // will be to pull directly from the posts table, etc
    $search_clause='';
    if(!empty($args['search'])) {
      $search_clause = $wpdb->prepare("
         AND ( p.post_title LIKE %s
               OR p.post_content LIKE %s )
      ",
      '%'.$args['search'].'%',
      '%'.$args['search'].'%');
    }

    $custom_clauses = $this->get_data_query_custom_clauses($args);

    $limit_statement='';
    if(!$count && (int)$args['per_page'] !== -1) {
      $limit_statement = $wpdb->prepare("
        LIMIT %d OFFSET %d
      ",
      (int)$args['per_page'],
      (((int)$args['page']-1) * (int)$args['per_page']));
    }

    $order_statement = "ORDER BY p.{$args['order']} {$args['order_dir']}";

    $select_vars = ($count ? 'COUNT(*)' : 'p.ID');

    $join = '';
    if(!isset($args['id'])) {
      $join = "
        JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
        JOIN {$coursedb->sections} AS s ON pm.meta_value = s.id
      ";
    }

    $q = "
      SELECT {$select_vars}
        FROM {$wpdb->posts} AS p
        {$join}
       WHERE p.post_status='publish'
         {$post_type_clause}
         {$id_clause}
         {$search_clause}
         {$custom_clauses}
       {$order_statement}
       {$limit_statement}
    ";

    return $q;
  }

  /**
   * Used to implement custom search args
   *
   * @param Array $args
   * @return string
   */
  protected function get_data_query_custom_clauses(Array $args) {
    global $wpdb;
    $clauses='';

    // No custom clauses for single lesson
    if(isset($args['id'])) {
      return $clauses;
    }

    if(isset($args['category']) && is_string($args['category'])) {
      $categories = array_map('trim', explode(',', $args['category']));
      $placeholders = implode(', ', array_fill(0, count($categories), '%s'));

      $clauses .= $wpdb->prepare(
        "
          AND EXISTS (
            SELECT 1
            FROM {$wpdb->term_relationships} tr
            INNER JOIN {$wpdb->term_taxonomy} tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
            INNER JOIN {$wpdb->terms} t ON (tt.term_id = t.term_id)
            WHERE tr.object_id = p.ID
              AND tt.taxonomy = %s
              AND t.name IN ($placeholders)
          )
        ",
        array_merge(['mpcs-curriculum-categories'], $categories) // First, pass the taxonomy, then the category names
      );
    }

    if(isset($args['tag']) && is_string($args['tag'])) {
      $tags = array_map('trim', explode(',', $args['tag']));
      $placeholders = implode(', ', array_fill(0, count($tags), '%s'));

      $clauses .= $wpdb->prepare(
        "
          AND EXISTS (
            SELECT 1
            FROM {$wpdb->term_relationships} tr
            INNER JOIN {$wpdb->term_taxonomy} tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
            INNER JOIN {$wpdb->terms} t ON (tt.term_id = t.term_id)
            WHERE tr.object_id = p.ID
              AND tt.taxonomy = %s
              AND t.name IN ($placeholders)
          )
        ",
        array_merge(['mpcs-curriculum-tags'], $tags) // First, pass the taxonomy, then the category names
      );
    }

    if(isset($args['author']) && is_numeric($args['author'])) {
      $clauses .= $wpdb->prepare(
        "
          AND p.post_author = %d
        ",
        $args['author']
      );
    }

    if(isset($args['course_id']) && is_numeric($args['course_id'])) {
      $clauses .= $wpdb->prepare("
        AND pm.meta_key = '_mpcs_lesson_section_id'
        AND s.course_id = %d
      ", $args['course_id']);
    }

    if(isset($args['section_id']) && is_numeric($args['section_id'])) {
      $clauses .= $wpdb->prepare("
        AND pm.meta_key = '_mpcs_lesson_section_id'
        AND pm.meta_value = %d
      ", $args['section_id']);
    }

    return $clauses;
  }

  /**
   * Prepare the item for the REST response
   *
   * @param mixed $item WordPress representation of the item.
   * @param WP_REST_Request $request Request object.
   * @return mixed
   */
  public function trim_obj(Array $lesson) {
    if(isset($lesson['emails'])) {
      unset($lesson['emails']);
    }

    if (isset($lesson['author']) && is_array($lesson['author'])) {
      if (isset($lesson['author']['email'])) {
        unset($lesson['author']['email']);
      }
    }

    return $lesson;
  }


  /**
   * Extend the object with additional data
   *
   * @param Array $object The object to extend.
   * @param Array $args Additional arguments.
   * @return Array
   */
  protected function extend_obj(Array $object, Array $args=array()) {
    if(!isset($object['id']) || !is_numeric($object['id'])) {
      return $object;
    }

    // Get the thumbnail
    $size = apply_filters('mpcs_course_thumbnail_size', 'mpcs-course-thumbnail');
    $object['thumbnail_url'] = get_the_post_thumbnail_url($object['id'], $size);

    // Add completed status
    if(isset($args['id'], $args['user_id']) && is_numeric($args['id']) && is_numeric($args['user_id'])) {
      $user_id = absint($args['user_id']);
      $lesson_id = absint($args['id']);
      $object['completed'] = courses\models\UserProgress::has_completed_lesson($user_id, $lesson_id);
    }

    // Lesson access status
    if (is_user_logged_in()) {
      $user = new MeprUser(get_current_user_id());
      $lesson = new courses\models\Lesson($object['id']);

      if(isset($lesson->ID) && $lesson->ID > 0) {
        $course = $lesson->course();
        $has_no_access = MeprRule::is_locked_for_user($user, $course);
        $object['locked'] = $has_no_access;

        if($has_no_access){
          $mepr_options = MeprOptions::fetch();
          $object['content'] = $mepr_options->unauthorized_message;
        }
      }
    }

    // Single Lesson
    if(isset($args['id']) && is_numeric($args['id'])) {
      // Get categories and tags
      $categories = get_the_terms($args['id'], 'mpcs-curriculum-categories');
      $tags = get_the_terms($args['id'], 'mpcs-curriculum-tags');

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

    // add permalink
    $object['url'] = get_permalink($object['id']);

    return $object;
  }

}
