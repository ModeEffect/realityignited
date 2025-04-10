<?php
if (!defined('ABSPATH')) {
  die('You are not allowed to call this page directly.');
}

use \memberpress\courses;
use \memberpress\quizzes;

class MpdtQuizzesApi extends MpdtBaseApi
{

  protected function register_more_routes()
  {
    register_rest_route($this->utils->namespace, '/' . $this->utils->base . '/(?P<id>[\d]+)/start', array(
      array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => array($this, 'mark_quiz_as_started'),
        'permission_callback' => array($this, 'rest_permissions_check'),
        'args'                => array()
      ),
    ));
    register_rest_route($this->utils->namespace, '/' . $this->utils->base . '/(?P<id>[\d]+)/complete', array(
      array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => array($this, 'mark_quiz_as_completed'),
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
  public function mark_quiz_as_started($request)
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

    $lesson = new quizzes\models\Quiz($lesson_id);
    if (!$lesson->ID) {
      return new WP_Error('mepr_not_found', 'Lesson not found.', array('status' => 404));
    }

    $user = new MeprUser($user_id);
    if (!$user->ID) {
      return new WP_Error('mepr_not_found', 'User not found.', array('status' => 404));
    }

    if (courses\models\UserProgress::has_completed_lesson($user_id, $lesson_id)) {
      return new WP_Error('mepr_already_completed', 'User has already completed this lesson.', array('status' => 400));
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
  public function mark_quiz_as_completed($request)
  {
    $params = $request->get_params();

    try {
      $this->check_dependency();
    } catch (Throwable $e) {
      return new WP_Error('mepr_error', $e->getMessage(), array('status' => 500));
    }

    // Validate quiz ID
    $quiz_id = absint($params['id']);
    if (!$quiz_id) {
      return new WP_Error('mepr_invalid_param', __('Invalid Quiz ID.', 'memberpress-course-quizzes', 'memberpress-developer-tools'), array('status' => 400));
    }

    // Validate user ID
    $user_id = absint($params['user_id']);
    if (is_user_logged_in() && !current_user_can('manage_options')) {
      $user_id = get_current_user_id();
    }

    if (!$user_id) {
      return new WP_Error('mepr_invalid_param', __('Invalid User ID.', 'memberpress-course-quizzes', 'memberpress-developer-tools'), array('status' => 400));
    }

    // Fetch quiz and user details
    $quiz = new quizzes\models\Quiz($quiz_id);
    if (!$quiz->ID) {
      return new WP_Error('mepr_not_found', __('Quiz not found', 'memberpress-course-quizzes', 'memberpress-developer-tools'), array('status' => 404));
    }

    $user = new MeprUser($user_id);
    if (!$user->ID) {
      return new WP_Error('mepr_not_found', __('User not found', 'memberpress-course-quizzes', 'memberpress-developer-tools'), array('status' => 404));
    }

    // Check if the user has already completed this quiz
    if (courses\models\UserProgress::has_completed_lesson($user_id, $quiz_id) && false === $quiz->allow_retakes) {
      return new WP_Error('mepr_already_completed', __('User has already completed this quiz.', 'memberpress-course-quizzes', 'memberpress-developer-tools'), array('status' => 400));
    }

    // Process quiz
    $quiz_ctrl = new quizzes\controllers\Quizzes();

    $attempt = quizzes\models\Attempt::get_one([
      'quiz_id' => $quiz_id,
      'user_id' => $user_id,
    ]);

    if (!$attempt instanceof quizzes\models\Attempt) {
      $attempt = new quizzes\models\Attempt();
      $attempt->quiz_id = $quiz_id;
      $attempt->user_id = $user_id;
      $attempt->status = quizzes\models\Attempt::$draft_str;
      $attempt->started_at = gmdate('Y-m-d H:i:s');
      $attempt->store();
    }

    try {
      $result = $quiz_ctrl->process_quiz_submission($params, $quiz, $attempt, $user);

      if (is_wp_error($result)) {
        return $result;
      }
    } catch (Throwable $e) {
      error_log($e->getMessage());
      return new WP_Error('mepr_error', $e->getMessage(), array('status' => 500));
    }

    // Return success response
    $response = rest_ensure_response([
      'success' => true,
      'attempt_id' => $attempt->id,
      'score' => $attempt->score,
      'score_summary' => $attempt->get_score(),
      'completed' => courses\models\UserProgress::has_completed_lesson($user_id, $quiz_id),
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
    if (!class_exists(courses\models\Lesson::class)) {
      throw new Exception(esc_html__('MemberPress Course Quizzes is not active.', 'memberpress-developer-tools'));
    }
  }
}
