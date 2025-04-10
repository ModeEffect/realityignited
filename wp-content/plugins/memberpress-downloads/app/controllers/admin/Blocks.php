<?php
namespace memberpress\downloads\controllers\admin;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'You are not allowed to call this page directly.' );
}

use memberpress\downloads as base,
    memberpress\downloads\lib as lib,
    memberpress\downloads\models as models,
    memberpress\downloads\helpers\Files as FilesHelper;

/**
 * This class handles the registrations and enqueues for MemberPress blocks
 */
class Blocks extends lib\BaseCtrl {

  public function load_hooks() {

    // Only load block stuff when Gutenberg is active (e.g. WordPress 5.0+)
    if ( function_exists( 'register_block_type' ) ) {
      add_action( 'init', array( $this, 'register_block_types_serverside' ) );
      add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_scripts' ) );
    }
  }

  /**
   * Render the frontend for the blocks on the server ("save" method must return null)
   *
   * @return void
   */
  public function register_block_types_serverside() {

    // Download block
    register_block_type(
      'memberpress/memberpress-download',
      array(
        'attributes' => array(
          'download' => array(
            'type' => 'string',
          ),
          'isList' => array(
            'type' => 'boolean',
            'default' => true,
          ),
          'listBy' => array(
            'type' => 'string',
            'default' => 'all',
          ),
          'limit' => array(
            'type' => 'string'
          ),
          'category' => array(
            'type' => 'string'
          ),
          'tag' => array(
            'type' => 'string'
          ),
          'showDescription' => array(
            'type' => 'boolean',
            'default' => false
          )
        ),
        'render_callback' => array( $this, 'render_download_block' ),
      )
    );
  }

  /**
   * Renders the download block.
   *
   * @param array   $props    Properties/data from the block
   *
   * @return string
   */
  public function render_download_block( $props ) {

    if ( isset( $props['isList'] ) && true == $props['isList'] ) {

      $shortcode = "[mpdl-file-links";

      if ( isset( $props['listBy'] ) && isset( $props[$props['listBy']] ) ) {
        $shortcode .= sprintf( ' %s=%s', $props['listBy'], $props[$props['listBy']] );
      }

      if ( isset( $props['limit'] ) ) {
        $shortcode .= sprintf( ' limit=%d', $props['limit'] );
      }

      if ( isset( $props['showDescription'] ) && true == $props['showDescription'] ) {
        $shortcode .= ' show_description=true';
      }

      $shortcode .= " ]";

    } else {

      $shortcode = sprintf( "[mpdl-file-link file_id=%s", $props['download'] );

      if ( isset( $props['showDescription'] ) && true == $props['showDescription'] ) {
        $shortcode .= ' show_description=true';
      }

      $shortcode .= "]";

    }

    ob_start();

    echo do_shortcode( $shortcode );

    return ob_get_clean();
  }

  /**
   * Enqueue the necessary JS in the editor
   *
   * @return void
   */
  public function enqueue_block_scripts() {
    $asset = include base\PATH . '/public/build/blocks.asset.php';

    wp_enqueue_script(
      'memberpress/mpdl-blocks',
      base\URL . '/public/build/blocks.js',
      array_merge(['memberpress/blocks'], $asset['dependencies']),
      $asset['version']
    );

    $cats = get_terms( array(
      'taxonomy' => models\File::$file_category_ctax
    ) );

    $categories = array();

    foreach ( $cats as $cat ) {
      $categories[] = array(
        'value' => $cat->term_id,
        'label' => $cat->name
      );
    }

    $tgs = get_terms( array(
      'taxonomy' => models\File::$file_tag_ctax
    ) );

    $tags = array();

    foreach ( $tgs as $tg ) {
      $tags[] = array(
        'value' => $tg->term_id,
        'label' => $tg->name
      );
    }

    wp_localize_script( 'memberpress/mpdl-blocks', 'mpdlBlocks', array(
      'downloads' => FilesHelper::get_downloads( true ),
      'categories' => $categories,
      'tags' => $tags
    ) );
  }
}
