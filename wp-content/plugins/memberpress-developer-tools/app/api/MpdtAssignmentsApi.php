<?php
if (!defined('ABSPATH')) {
  die('You are not allowed to call this page directly.');
}

use \memberpress\courses;
use \memberpress\assignments;

class MpdtAssignmentsApi extends MpdtBaseApi
{

  protected function register_more_routes()
  {
    register_rest_route($this->utils->namespace, '/' . $this->utils->base . '/(?P<id>[\d]+)/start', array(
      array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => array($this, 'mark_assignment_as_started'),
        'permission_callback' => array($this, 'rest_permissions_check'),
        'args'                => array()
      ),
    ));
    register_rest_route($this->utils->namespace, '/' . $this->utils->base . '/(?P<id>[\d]+)/complete', array(
      array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => array($this, 'mark_assignment_as_completed'),
        'permission_callback' => array($this, 'rest_permissions_check'),
        'args'                => array()
      ),
    ));
    register_rest_route($this->utils->namespace, '/' . $this->utils->base . '/(?P<id>[\d]+)/submissions/(?P<user_id>[\d]+)', array(
      array(
        'methods'             => WP_REST_Server::DELETABLE,
        'callback'            => array($this, 'delete_assignment_submission'),
        'permission_callback' => array($this, 'rest_permissions_check'),
        'args'                => array()
      ),
    ));
  }

  /**
   * Used to implement actions before getting items
   *
   * @param Array $args The search args
   * @param WP_REST_Request $request
   *
   * @return WP_Error|WP_REST_Request
   */
  protected function before_get_items($args, $request)
  {
    if (!isset($args['section_id']) && !isset($args['course_id'])) {
      return new WP_Error('missing_param', 'The course_id or section_id parameter is required.', array('status' => 400));
    }

    // if both are set, return error
    if (isset($args['section_id']) && isset($args['course_id'])) {
      return new WP_Error('too_many_params', 'You can only use the course_id or section_id parameter, not both.', array('status' => 400));
    }

    return $request;
  }

  /**
   * Mark a lesson as started
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function mark_assignment_as_started($request)
  {
    try {
      $this->check_dependency();
    } catch (Throwable $e) {
      return new WP_Error('mepr_error', $e->getMessage(), array('status' => 500));
    }

    $lesson_id = $request->get_param('id');
    $user_id = $request->get_param('user_id');

    if (is_user_logged_in() && !current_user_can('manage_options')) {
      $user_id = get_current_user_id();
    }

    if (!is_numeric($lesson_id)) {
      return new WP_Error('mepr_invalid_param', 'Invalid Course ID.', array('status' => 400));
    }

    if (!is_numeric($user_id)) {
      return new WP_Error('mepr_invalid_param', 'Invalid User ID.', array('status' => 400));
    }

    $lesson = new assignments\models\Assignment($lesson_id);
    if (!$lesson->ID) {
      return new WP_Error('mepr_not_found', 'Assignment not found.', array('status' => 404));
    }

    $user = new MeprUser($user_id);
    if (!$user->ID) {
      return new WP_Error('mepr_not_found', 'User not found.', array('status' => 404));
    }

    if (courses\models\UserProgress::has_completed_lesson($user_id, $lesson_id)) {
      return new WP_Error('mepr_already_completed', 'User has already completed this assignment.', array('status' => 400));
    }

    $user = new MeprUser($user_id);
    \MeprEvent::record(
      'mpca-lesson-started',
      $user,
      array(
        'lesson_id' => $lesson_id,
      )
    );
    update_user_meta($user_id, 'mpcs_lesson_started_' . $lesson_id, MeprUtils::ts_to_mysql_date(time()));

    $response = rest_ensure_response(['success' => true]);

    if (is_wp_error($response)) {
      return $response;
    }

    $response->set_status(200);
    return $response;
  }

  /**
   * Complete a Lesson
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function mark_assignment_as_completed($request)
  {
    $params = $request->get_params();

    try {
      $this->check_dependency();
    } catch (Throwable $e) {
      return new WP_Error('mepr_error', $e->getMessage(), array('status' => 500));
    }

    // Validate quiz ID
    $assignment_id = isset($params['id']) ? absint($params['id']) : null;
    if (!$assignment_id) {
      return new WP_Error('mepr_invalid_param', __('Invalid Assignment ID.', 'memberpress-developer-tools'), array('status' => 400));
    }
    $params['post_id'] = $assignment_id;

    // Validate user ID
    $user_id = isset($params['user_id']) ? absint($params['user_id']) : null;
    if (is_user_logged_in() && !current_user_can('manage_options')) {
      $user_id = get_current_user_id();
    }

    if (!$user_id) {
      return new WP_Error('mepr_invalid_param', __('Invalid User ID.', 'memberpress-developer-tools'), array('status' => 400));
    }

    // Fetch quiz and user details
    $assignment = new assignments\models\Assignment($assignment_id);
    if (!$assignment->ID) {
      return new WP_Error('mepr_not_found', __('Assignment not found', 'memberpress-developer-tools'), array('status' => 404));
    }

    $user = new MeprUser($user_id);
    if (!$user->ID) {
      return new WP_Error('mepr_not_found', __('User not found', 'memberpress-developer-tools'), array('status' => 404));
    }

    // Check if the user has already completed this assignment
    if (courses\models\UserProgress::has_completed_lesson($user_id, $assignment_id) && false === $assignment->allow_resubmission) {
      return new WP_Error('mepr_already_completed', __('User has already completed this assignment.', 'memberpress-developer-tools'), array('status' => 400));
    }

    // Get assignment controller
    $assignment_ctrl = new assignments\controllers\AssignmentsCtrl();

    // if text, upload_file and upload_url are set, return error
    if (isset($params['text'], $_FILES['upload_file'], $params['upload_url'])) {
      return new WP_Error('mepr_invalid_param', __('You can only submit text, upload a file, or submit a URL.', 'memberpress-developer-tools'), array('status' => 400));
    }

    // if any two are set, then return error
    if ((isset($params['text'], $_FILES['upload_file'])) || (isset($params['text'], $params['upload_url'])) || (isset($_FILES['upload_file'], $params['upload_url']))) {
      return new WP_Error('mepr_invalid_param', __('You can only submit text, upload a file, or submit a URL.', 'memberpress-developer-tools'), array('status' => 400));
    }

    // if none is set, return error
    if (!isset($params['text']) && !isset($_FILES['upload_file']) && !isset($params['upload_url'])) {
      return new WP_Error('mepr_invalid_param', __('You must submit text, upload a file, or submit a URL.', 'memberpress-developer-tools'), array('status' => 400));
    }

    if (isset($params['text'])) {
      $params['content'] = $params['text'];
      $params['text'] = wp_strip_all_tags($params['text']);
    }

    if (isset($_FILES['upload_file']) && !empty($_FILES['upload_file']['tmp_name'])) {
      $params['upload_url'] = $assignment_ctrl->process_file_upload($_FILES['upload_file'], $assignment);
    }

    if (is_wp_error($params['upload_url'])) {
      return $params['upload_url'];
    }

    // Process assignment submission
    try {
      $result = $assignment_ctrl->process_assignment_submission($params, $user_id);

      if (is_wp_error($result)) {
        return $result;
      }
    } catch (Throwable $e) {
      error_log($e->getMessage());
      return new WP_Error('mepr_error', $e->getMessage(), array('status' => 500));
    }

    $submission = assignments\models\Submission::get_one([
      'assignment_id' => $assignment_id,
      'user_id' => $user_id,
    ]);

    // Return success response
    $response = rest_ensure_response([
      'success' => true,
      'submission' => $submission->get_values(),
      'score' => $submission->score,
      'score_summary' => $submission->get_score(),
      'completed' => courses\models\UserProgress::has_completed_lesson($user_id, $assignment_id),
    ]);

    if (is_wp_error($response)) {
      return $response;
    }

    $response->set_status(200);
    return $response;
  }

  /**
   * Delete an assignment submission
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function delete_assignment_submission($request)
  {

    try {
      $this->check_dependency();
    } catch (Throwable $e) {
      return new WP_Error('mepr_error', $e->getMessage(), array('status' => 500));
    }

    $params = $request->get_params();

    // Validate quiz ID
    $assignment_id = isset($params['id']) ? absint($params['id']) : null;
    if (!$assignment_id) {
      return new WP_Error('mepr_invalid_param', __('Invalid Assignment ID.', 'memberpress-developer-tools'), array('status' => 400));
    }

    // Validate user ID
    $user_id = isset($params['user_id']) ? absint($params['user_id']) : null;
    if (is_user_logged_in() && !current_user_can('manage_options')) {
      $user_id = get_current_user_id();
    }

    if (!$user_id) {
      return new WP_Error('mepr_invalid_param', __('Invalid User ID.', 'memberpress-developer-tools'), array('status' => 400));
    }

    // Fetch quiz and user details
    $assignment = new assignments\models\Assignment($assignment_id);
    if (!$assignment->ID) {
      return new WP_Error('mepr_not_found', __('Assignment not found', 'memberpress-developer-tools'), array('status' => 404));
    }

    try {
      $submission = assignments\models\Submission::get_one([
        'assignment_id' => $assignment_id,
        'user_id' => $user_id,
      ]);

      if (!$submission) {
        return new WP_Error('mepr_not_found', __('Submission not found', 'memberpress-developer-tools'), array('status' => 404));
      }

      $submission->destroy();
    } catch (Throwable $e) {
      error_log($e->getMessage());
      return new WP_Error('mepr_error', $e->getMessage(), array('status' => 500));
    }

    // Delete user progress
    try {
      $user_progress = courses\models\UserProgress::find_one_by_user_and_lesson($user_id, $assignment_id);

      if (!$user_progress) {
        return new WP_Error('mepr_not_found', __('User Progress not found', 'memberpress-developer-tools'), array('status' => 404));
      }
      $user_progress->destroy();
    } catch (Throwable $e) {
      error_log($e->getMessage());
      return new WP_Error('mepr_error', $e->getMessage(), array('status' => 500));
    }

    // Return success response
    $response = rest_ensure_response([
      'success' => esc_html__('Submission deleted successfully', 'memberpress-developer-tools'),
    ]);

    if (is_wp_error($response)) {
      return $response;
    }

    $response->set_status(200);
    return $response;
  }

  /**
   * Check if the dependency is active. If not, return an error
   *
   * @return void
   */
  protected function check_dependency()
  {
    if (!class_exists(assignments\models\Assignment::class)) {
      throw new Exception(esc_html__('MemberPress Course Assignments is not active.', 'memberpress-developer-tools'));
    }
  }
}
