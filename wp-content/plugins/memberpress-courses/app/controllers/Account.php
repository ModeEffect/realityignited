<?php
namespace memberpress\courses\controllers;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use memberpress\courses as base;
use memberpress\courses\lib as lib;
use memberpress\courses\models as models;
use memberpress\courses\helpers as helpers;

class Account extends lib\BaseCtrl {
  public function load_hooks() {
    add_action('mepr_account_nav', array($this, 'my_courses_nav'));
    add_action('mepr_account_nav_content', array($this, 'my_courses_list'));
    add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
  }

  /**
  * Enqueue scripts for account controller
  * @see load_hooks(), add_action('wp_enqueue_scripts')
  */
  public static function enqueue_scripts() {
    global $post;

    if(is_a($post, 'WP_Post') && \MeprUser::is_account_page($post)) {
      \wp_enqueue_style('mpcs-simplegrid', base\CSS_URL . '/vendor/simplegrid.css', array(), base\VERSION);
      \wp_enqueue_style('mpcs-progress', base\CSS_URL . '/progress.css', array(), base\VERSION);
      \wp_enqueue_script('mpcs-progress-js', base\JS_URL . '/progress.js', array('jquery'), base\VERSION);
    }
  }

  /**
  * Enqueue scripts for admin user profile
  * @see load_hooks(), add_action('admin_enqueue_scripts')
  * @param string $hook Current admin page
  */
  public static function enqueue_admin_scripts($hook) {
    if($hook === 'user-edit.php') {
      \wp_enqueue_style('mpcs-progress', base\CSS_URL . '/progress.css', array(), base\VERSION);
      \wp_enqueue_script('mpcs-progress-js', base\JS_URL . '/progress.js', array('jquery'), base\VERSION);
    }
  }

  /**
  * Render courses nav
  * @see load_hooks(), add_action('mepr_account_nav')
  * @param \MeprUser $current_user logged in MeprUser object
  */
  public static function my_courses_nav($current_user) {
    global $post;
    $account_url = lib\Utils::get_permalink($post->ID);
    $delim = preg_match('#\?#', $account_url) ? '&' : '?';
    ob_start();
    ?>
    <span class="mepr-nav-item mepr-courses <?php \MeprAccountHelper::active_nav(\apply_filters('mepr-account-nav-courses-active-name', 'courses')); ?>">
      <a href="<?php echo \apply_filters('mepr-account-nav-courses-link', $account_url . $delim . 'action=courses'); ?>" id="mepr-account-courses">
        <?php echo \apply_filters('mepr-account-nav-courses-label', __('Courses', 'memberpress-courses')); ?>
      </a>
    </span>
    <?php
    echo \apply_filters('mpcs_account_nav_courses_output', ob_get_clean(), $current_user);
  }

  /**
  * Render courses list
  *
  * @see load_hooks(), add_action('mepr_account_nav_content')
  * @param string $action Account page current action
  * @param boolean $show_bookmark Show progress bar
  * @param array $attributes Attributes for mpcs-my-courses shortcode
  */
  public static function my_courses_list($action, $show_bookmark = true, $attributes = array()) {
    global $post, $paged;

    $mepr_options = \MeprOptions::fetch();
    $options = get_option('mpcs-options');

    $sort_options = array(
        'alphabetically' => array(
            'orderby' => 'title',
        ),
        'last-updated' => array(
            'orderby' => 'modified',
        ),
        'publish-date' => array(
            'orderby' => 'date',
        )
    );

    $mpcs_sort_order = helpers\Options::val($options,'courses-sort-order', 'alphabetically');
    $mpcs_sort_order_direction = helpers\Options::val($options,'courses-sort-order-direction', 'ASC');

    if( ! in_array($mpcs_sort_order_direction, array('ASC','DESC'), true) ) {
      $mpcs_sort_order_direction = 'ASC';
    }

    $sort_option = isset($sort_options[$mpcs_sort_order]) ? $sort_options[$mpcs_sort_order] : $sort_options['default'];

    $my_courses = array();

    // Check if it's the front page and adjust paged variable
    if (is_front_page()) {
      $paged = (get_query_var('page')) ? get_query_var('page') : 1;
    } else {
      $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    }

    $per_page = (int) helpers\Options::val($options,'courses-per-page', 10);
    $post_args = array(
      'post_type' => models\Course::$cpt,
      'post_status' => 'publish',
      'posts_per_page' => '-1',
      'orderby'=> $sort_option['orderby'],
      'order' => $mpcs_sort_order_direction
    );

    $courses = get_posts($post_args);

    if(is_user_logged_in() && ($action === 'courses' || ($action === 'courses_shortcode' && is_array($attributes) && in_array('hide_protected', $attributes)))) {
      $current_user = lib\Utils::get_currentuserinfo();
      $mepr_user    = new \MeprUser( $current_user->ID );

      if ( false == \MeprUtils::is_logged_in_and_an_admin() ) {
        $courses = array_filter( $courses, function ( $course ) use ( $mepr_user ) {
          return false == \MeprRule::is_locked_for_user( $mepr_user, $course );
        } );
      }
    }

    if(!is_user_logged_in() && $action === 'courses_shortcode' && is_array($attributes) && in_array('hide_protected', $attributes)) {
      $courses = array_filter( $courses, function ( $course ) {
        return false == \MeprRule::is_locked( $course );
      } );
    }

    if($action === 'courses' || $action === 'courses_shortcode') {
      $courses_ids = array_map(function($c) {
        return is_object($c) ? $c->ID : $c['ID'];
      }, $courses);
      $per_page = apply_filters('mpcs_courses_per_page', $per_page);

      if (empty($courses_ids)) {
        $courses_ids = array ( 0 );
      }

      $args = array(
        'post_type' => models\Course::$cpt,
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        'paged' => $paged,
        'orderby'=> 'post__in',
        'order' => 'ASC',
        'post__in' => $courses_ids
      );
      if (!empty($attributes) && isset($attributes['categories']) && !empty($attributes['categories'])) {
        $terms = explode( ',', trim( $attributes['categories'] ) );
        $args['tax_query'] = array(
          'relation' => 'OR',
          array('taxonomy' => 'mpcs-course-categories', 'field' => 'slug', 'terms' => $terms)
        );
      }
      if (!empty($attributes) && isset($attributes['tags']) && !empty($attributes['tags'])) {
        $terms = explode( ',', trim( $attributes['tags'] ) );
        $args['tax_query'] = array(
          'relation' => 'OR',
          array('taxonomy' => 'mpcs-course-tags', 'field' => 'slug', 'terms' => $terms)
        );
      }

      $course_query = new \WP_Query($args);
      $course_posts = $course_query->get_posts();

      foreach ($course_posts as $course) {
        $my_courses[] = new models\Course($course->ID);
      }

      if ($action === 'courses_shortcode') {
        $attributes = !empty($attributes) ? $attributes : [];
        \MeprView::render('/courses/courses_list', get_defined_vars());
      } elseif (isset($mepr_options->design_enable_account_template) && $mepr_options->design_enable_account_template) {
        \MeprView::render('/readylaunch/account/courses_list', get_defined_vars());
      } else {
        \MeprView::render('/account/courses_list', get_defined_vars());
      }
    }
  }
}
