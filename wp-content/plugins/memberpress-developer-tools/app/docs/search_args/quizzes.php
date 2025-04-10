<?php if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

// The format of this array is determined by WP-API
return array(
  'course_id' => array(
    'description'        => __('Get lessons of a given course', 'memberpress-developer-tools'),
    'type'               => 'integer',
    'sanitize_callback'  => 'absint',
  ),
  'section_id' => array(
    'description'        => __('Get lessons of a given section', 'memberpress-developer-tools'),
    'type'               => 'integer',
    'sanitize_callback'  => 'absint',
  ),
  'category' => array(
    'description'        => __('Filter results by categories. You can specify multiple categories, separated by commas.', 'memberpress-developer-tools'),
    'type'               => 'string',
    'sanitize_callback'  => 'sanitize_text_field',
  ),
  'tag' => array(
    'description'        => __('Filter results by a specific tag. You can specify multiple tags, separated by commas.', 'memberpress-developer-tools'),
    'type'               => 'string',
    'sanitize_callback'  => 'sanitize_text_field',
  ),
  'author' => array(
    'description'        => __('Filter results by the author\'s ID.', 'memberpress-developer-tools'),
    'type'               => 'integer',
    'sanitize_callback'  => 'absint',
  ),
);
