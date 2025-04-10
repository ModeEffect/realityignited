<?php if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

return array(
  // "start_{$this->class_info->singular}" => (object)array(
  //   'name'   => sprintf(__('Start %s', 'memberpress-developer-tools'), MpdtInflector::humanize($this->class_info->singular)),
  //   'desc'   => sprintf(__('Starts a %s with the given id.', 'memberpress-developer-tools'), MpdtInflector::humanize($this->class_info->singular)),
  //   'method' => 'POST',
  //   'url'    => rest_url($this->namespace.'/'.$this->base) . '/:id/start',
  //   'auth'   => true,
  //   'search_args'  => __('None', 'memberpress-developer-tools'),
  //   'update_args'  => $this->accept_fields,
  //   'output' => __('JSON', 'memberpress-developer-tools'),
  //   'resp'   => __('Lesson started', 'memberpress-developer-tools')
  // ),
  // "complete_lesson" => (object)array(
  //   'name'   => __('Mark Lesson as Complete', 'memberpress-developer-tools'),
  //   'desc'   => __('Marks the specified lesson as completed.', 'memberpress-developer-tools'),
  //   'method' => 'POST',
  //   'url'    => rest_url($this->namespace.'/'.$this->base) . '/:id/cancel',
  //   'auth'   => true,
  //   'search_args'  => __('None', 'memberpress-developer-tools'),
  //   'update_args'  => __('None', 'memberpress-developer-tools'),
  //   'output' => __('JSON', 'memberpress-developer-tools'),
  //   'resp'   => (object)array(
  //     'utils_class' => $this->class_info->singular,
  //     'single_result' => true,
  //     'count' => 1,
  //     'custom_response' => array(
  //       'message' => __('The lesson has been successfully marked as complete.', 'memberpress-developer-tools')
  //     )
  //   )
  // ),
);
