<?php
namespace memberpress\downloads\controllers\admin;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use memberpress\downloads as base,
    memberpress\downloads\models as models,
    memberpress\downloads\helpers as helpers,
    memberpress\downloads\lib as lib;

class RestApi extends lib\BaseCtrl {

  public function load_hooks() {
    add_action('rest_api_init', array($this, 'register_routes'));
  }

  /**
   * Register the routes for the objects of the controller.
   */
  public function register_routes() {
    register_rest_route( base\SLUG_KEY, '/downloads/files', array(
      array(
        'methods'             => \WP_REST_Server::READABLE,
        'callback'            => array( $this, 'fetch_downloads' ),
        'permission_callback' => array( $this, 'fetch_downloads_permissions_check' ),
      ),
    ) );
  }

  /**
   * Fetches all questions from custom questions table
   *
   * @param \WP_REST_Request $request Full data about the request.
   * @return \WP_REST_Response
   */
  public function fetch_downloads($request) {
    $downloads = self::fetch_items( $request, 'mpdl-file', 'downloads');
    return $downloads;
  }


  /**
   * Check if a given request has access to get items
   *
   * @return bool
   */
  public function fetch_downloads_permissions_check() {
    return current_user_can( 'manage_options' ); // Not open to public yet
  }

  /**
   * Get a collection of items
   *
   * @param \WP_REST_Request $request Full data about the request.
   * @param string $post_type
   * @param string $data_key
   * @return \WP_REST_Response
   */
  public function fetch_items( $request, $post_type, $data_key ) {
    $params = $request->get_params();

    $args = [
      'post_type' => $post_type,
      'fields' => 'ids',
      's' => isset($params['s']) && is_string($params['s']) ? sanitize_text_field($params['s']) : '',
      'paged' => isset($params['paged']) && is_numeric($params['paged']) ? max(1, (int) $params['paged']) : 1,
      'post_status' => isset($params['post_status']) && is_array($params['post_status']) ? array_map('sanitize_key', $params['post_status']) : ['publish', 'draft', 'future'],
      'posts_per_page' => isset($params['number']) ? absint($params['number']) : 10,
    ];

    $query = new \WP_Query($args);
    $post_ids = $query->get_posts();
    $data = array();
    $data[$data_key] = array();

    foreach($post_ids as $post_id) {
      if( $post_type == models\File::$cpt) {
        $post = new models\File($post_id);
        $data[$data_key][] = $this->prepare_item_for_response($post);
      }
    }

    $data['meta']['total'] = $query->found_posts;
    $data['meta']['max'] = $query->max_num_pages;
    $data['meta']['count'] = $query->post_count;

    return new \WP_REST_Response( $data, 200 );
  }

  /**
   * Prepare the item for the REST response
   *
   * @param models\File $file
   * @return array
   */
  public function prepare_item_for_response($file) {
    $is_image = \preg_match('/image\/\w+/', $file->filetype);

    $data = [
      'id' => $file->ID,
      'title' => $file->post_title,
      'url' => $file->url(),
      'type' => 'download',
      'thumb' => $is_image ? 'image' : 'icon',
    ];

    if($is_image){
      $data['thumb_url'] = $file->thumb_url();
    } else {
      $data['icon'] = helpers\Files::file_thumb($file->filetype);
    }

    return $data;
  }
}
