<?php
/*
Plugin Name: MemberPress Developer Tools
Plugin URI: http://www.memberpress.com/
Description: Tools for MemberPress Developers.
Version: 1.3.3
Author: Caseproof, LLC
Author URI: http://caseproof.com/
Text Domain: memberpress-developer-tools
Copyright: 2004-2024, Caseproof, LLC
*/

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

include_once ABSPATH . 'wp-admin/includes/plugin.php';

function mpdt_rest_api_available() {
  global $wp_version;
  return (
    version_compare($wp_version, '4.7', '>=') ||
    is_plugin_active('rest-api/plugin.php')
  );
}

// TODO: Eventually we want to give the user some feedback if the required plugins aren't installed/activated
if(is_plugin_active('memberpress/memberpress.php')) {
  // Set all path / url variables
  define('MPDT_PLUGIN_SLUG','memberpress-developer-tools/main.php');
  define('MPDT_PLUGIN_NAME','memberpress-developer-tools');
  define('MPDT_EDITION',MPDT_PLUGIN_NAME);
  define('MPDT_WEBHOOKS_KEY','mpdt_webhooks');
  define('MPDT_PATH',dirname(__DIR__).'/'.MPDT_PLUGIN_NAME);
  define('MPDT_API_PATH',MPDT_PATH.'/app/api');
  define('MPDT_LIB_PATH',MPDT_PATH.'/app/lib');
  define('MPDT_CTRLS_PATH',MPDT_PATH.'/app/controllers');
  define('MPDT_UTILS_PATH',MPDT_PATH.'/app/utils');
  define('MPDT_DOCS_PATH',MPDT_PATH.'/app/docs');
  define('MPDT_VIEWS_PATH',MPDT_PATH.'/app/views');
  define('MPDT_IMAGES_PATH',MPDT_PATH.'/public/images');
  define('MPDT_URL',plugins_url('/'.MPDT_PLUGIN_NAME));
  define('MPDT_JS_URL',MPDT_URL.'/public/js');
  define('MPDT_CSS_URL',MPDT_URL.'/public/css');
  define('MPDT_IMAGES_URL',MPDT_URL.'/public/images');

  // Load all dependencies.
  require_once MPDT_PATH . '/autoload.php';

  MpdtCtrlFactory::load('admin');
  MpdtCtrlFactory::load('webhooks');

  if(mpdt_rest_api_available()) {
    MpdtCtrlFactory::load('api');
  }
}
