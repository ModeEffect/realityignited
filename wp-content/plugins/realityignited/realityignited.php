<?php
/**
 * Plugin name: Reality Ignited
 * Description: Custom website functionality
 * Version: 1.0.0
 * Author: ModeEffect
 * Author URI: https://modeeffect.com/
 * Text Domain: realityignited
 */

define('RI_PLUGIN_VERSION', '1.0.0');
define('RI_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
define('RI_PLUGIN_URL', plugin_dir_url( __FILE__ ));

add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style( 'realityignited', RI_PLUGIN_URL . '/assets/main.css', [], RI_PLUGIN_VERSION );
}, PHP_INT_MAX);

/**
 * Coming soon restrictions
 */
require_once RI_PLUGIN_DIR . '/includes/coming-soon/front.php';
require_once RI_PLUGIN_DIR . '/includes/coming-soon/admin.php';