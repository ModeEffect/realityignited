<?php if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}
use \memberpress\courses;
use \memberpress\quizzes;

class MpdtQuizUtils extends MpdtLessonUtils {
  public $model_class = quizzes\models\Quiz::class;

  /**
   * Extend the object with additional data
   *
   * @param Array $object The object to extend.
   * @param Array $args Additional arguments.
   * @return Array
   */
  protected function extend_obj(Array $object, Array $args=array()) {
    if(!isset($object['id']) || !is_numeric($object['id'])) {
      return $object;
    }

    // Get the thumbnail
    $size = apply_filters('mpcs_course_thumbnail_size', 'mpcs-course-thumbnail');
    $object['thumbnail_url'] = get_the_post_thumbnail_url($object['id'], $size);

    if (is_user_logged_in()) {
      $user = new MeprUser(get_current_user_id());
      $lesson = new quizzes\models\Quiz($object['id']);

      if(isset($lesson->ID) && $lesson->ID > 0) {
        $course = $lesson->course();
        $has_no_access = MeprRule::is_locked_for_user($user, $course);
        $object['locked'] = $has_no_access;

        if($has_no_access){
          $mepr_options = MeprOptions::fetch();
          $object['content'] = $mepr_options->unauthorized_message;
        }
      }
    }

    // Single Quiz
    if(isset($args['id']) && is_numeric($args['id'])) {
      // Get categories and tags
      $categories = get_the_terms($args['id'], 'mpcs-curriculum-categories');
      $tags = get_the_terms($args['id'], 'mpcs-curriculum-tags');
      $quiz = new quizzes\models\Quiz($args['id']);

      $category_data = array();
      $tag_data = array();

      // Check if categories are available and not an error
      if (! is_wp_error($categories) && ! empty($categories)) {
        foreach ($categories as $category) {
          $category_data[] = array(
            'id'   => $category->term_id,
            'name' => $category->name,
          );
        }
      }

      // Check if tags are available and not an error
      if (! is_wp_error($tags) && ! empty($tags)) {
        foreach ($tags as $tag) {
          $tag_data[] = array(
            'id'   => $tag->term_id,
            'name' => $tag->name,
          );
        }
      }

      $questions = (array) $quiz->get_questions();
      $questions = array_map(function($question) {
        return $question->get_values();
      }, $questions);

      $object['categories'] = $category_data;
      $object['tags'] = $tag_data;
      $object['questions'] = $questions;
    }

    // add attempts if there is user id
    if(isset($args['user_id']) && is_numeric($args['user_id'])) {
      $user_id = absint($args['user_id']);
      $lesson_id = absint($object['id']);

      // is there an attempt for this user?
      $attempt = quizzes\models\Attempt::get_one([
        'quiz_id' => $lesson_id,
        'user_id' => $user_id,
      ]);

      if($attempt instanceof quizzes\models\Attempt) {
        $object['attempt'] = $attempt->get_values();
      }
    }

    // Add completed status
    if(isset($args['id'], $args['user_id']) && is_numeric($args['id']) && is_numeric($args['user_id'])) {

      if($attempt instanceof quizzes\models\Attempt) {
        $answers = $attempt->get_answers();
        $object['answers'] = $answers;
        $object['completed'] = $attempt->is_complete();
        $object['score'] = sprintf('%d%%', $attempt->score);
        $object['score_summary'] = $attempt->get_score();
      }
    }

    // add permalink
    $object['url'] = get_permalink($object['id']);

    return $object;
  }


}
