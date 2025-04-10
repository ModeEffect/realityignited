<?php if (!defined('ABSPATH')) {
  die('You are not allowed to call this page directly.');
}

return array(
  'title' => array(
    'name' => esc_html__('Lesson Title', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => '',
    'required' => esc_html__('Required', 'memberpress-developer-tools'),
    'desc' => esc_html__('The title of the Course.', 'memberpress-developer-tools')
  ),

  'status' => array(
    'name' => esc_html__('Lesson Status', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => 'enabled',
    'required' => false,
    'desc' => esc_html__('The current status of the lesson. Can be "enabled" or "disabled".', 'memberpress-developer-tools')
  ),

  'section_id' => array(
    'name' => esc_html__('Section ID', 'memberpress-developer-tools'),
    'type' => 'integer',
    'default' => 0,
    'required' => false,
    'desc' => esc_html__('The ID of the section that this lesson belongs to.', 'memberpress-developer-tools')
  ),

  'lesson_order' => array(
    'name' => esc_html__('Lesson Order', 'memberpress-developer-tools'),
    'type' => 'integer',
    'default' => 0,
    'required' => false,
    'desc' => esc_html__('The order in which this lesson appears within its section.', 'memberpress-developer-tools')
  ),
);
