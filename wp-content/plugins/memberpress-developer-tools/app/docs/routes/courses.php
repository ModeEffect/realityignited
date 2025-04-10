<?php if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

return array(
  // "settings" => (object)array(
  //   'name'   => __('Get Courses Settings', 'memberpress-developer-tools'),
  //   'desc'   => __('Get all settings for the Courses Add-on.', 'memberpress-developer-tools'),
  //   'method' => 'GET',
  //   'url'    => rest_url($this->namespace.'/'.$this->base) . '/settings',
  //   'auth'   => true,
  //   'search_args'  => __('None', 'memberpress-developer-tools'),
  //   'update_args'  => __('None', 'memberpress-developer-tools'),
  //   'output' => __('JSON', 'memberpress-developer-tools'),
  //   'resp'   => (object)array(
  //     'utils_class' => $this->class_info->singular,
  //     'single_result' => true,
  //     'count' => 1
  //   )
  // ),
  // "authors" => (object)array(
  //   'name'   => __('Get All Course Authors', 'memberpress-developer-tools'),
  //   'desc'   => __('Get all course authors for the Courses Add-on.', 'memberpress-developer-tools'),
  //   'method' => 'GET',
  //   'url'    => rest_url($this->namespace.'/'.$this->base) . '/authors',
  //   'auth'   => true,
  //   'search_args'  => __('None', 'memberpress-developer-tools'),
  //   'update_args'  => __('None', 'memberpress-developer-tools'),
  //   'output' => __('JSON', 'memberpress-developer-tools'),
  //   'resp'   => (object)array(
  //     'utils_class' => $this->class_info->singular,
  //     'single_result' => true,
  //     'count' => 1
  //   )
  // ),
  // "get_{$this->class_info->singular}_sections" => (object)array(
  //   'name'   => sprintf(__('Get %s Sections', 'memberpress-developer-tools'), MpdtInflector::humanize($this->class_info->singular)),
  //   'desc'   => sprintf(__('Get titles and IDs of the sections and lessons of one %s with a given id.', 'memberpress-developer-tools'), MpdtInflector::humanize($this->class_info->singular)),
  //   'method' => 'GET',
  //   'url'    => rest_url($this->namespace.'/'.$this->base) . '/:id/sections',
  //   'auth'   => true,
  //   'search_args'  => __('None', 'memberpress-developer-tools'),
  //   'update_args'  => __('None', 'memberpress-developer-tools'),
  //   'output' => __('JSON', 'memberpress-developer-tools'),
  //   'resp'   => (object)array(
  //     'utils_class' => $this->class_info->singular,
  //     'single_result' => true,
  //     'count' => 1
  //   )
  // ),
  // "start_{$this->class_info->singular}" => (object)array(
  //   'name'   => sprintf(__('Start %s', 'memberpress-developer-tools'), MpdtInflector::humanize($this->class_info->singular)),
  //   'desc'   => sprintf(__('Starts a %s with the given id.', 'memberpress-developer-tools'), MpdtInflector::humanize($this->class_info->singular)),
  //   'method' => 'POST',
  //   'url'    => rest_url($this->namespace.'/'.$this->base) . '/:id/start',
  //   'auth'   => true,
  //   'search_args'  => __('None', 'memberpress-developer-tools'),
  //   'update_args'  => $this->accept_fields,
  //   'output' => __('JSON', 'memberpress-developer-tools'),
  //   'resp'   => __('Course started', 'memberpress-developer-tools')
  // ),
  // "complete_{$this->class_info->singular}" => (object)array(
  //   'name'   => sprintf(__('Complete %s', 'memberpress-developer-tools'), MpdtInflector::humanize($this->class_info->singular)),
  //   'desc'   => sprintf(__('Complete a %s with the given id.', 'memberpress-developer-tools'), MpdtInflector::humanize($this->class_info->singular)),
  //   'method' => 'POST',
  //   'url'    => rest_url($this->namespace.'/'.$this->base) . '/:id/complete',
  //   'auth'   => true,
  //   'search_args'  => __('None', 'memberpress-developer-tools'),
  //   'update_args'  => $this->accept_fields,
  //   'output' => __('JSON', 'memberpress-developer-tools'),
  //   'resp'   => __('Course completed', 'memberpress-developer-tools')
  // ),
);
