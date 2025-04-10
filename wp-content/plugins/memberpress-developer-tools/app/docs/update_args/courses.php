<?php if (!defined('ABSPATH')) {
  die('You are not allowed to call this page directly.');
}

return array(
  'title' => array(
    'name' => esc_html__('Course Title', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => '',
    'required' => esc_html__('Required', 'memberpress-developer-tools'),
    'desc' => esc_html__('The title of the Course.', 'memberpress-developer-tools')
  ),

  'status' => array(
    'name' => esc_html__('Course Status', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => 'enabled',
    'required' => false,
    'desc' => esc_html__('The current status of the course. Can be "enabled" or "disabled".', 'memberpress-developer-tools')
  ),

  'include_in_course_listing' => array(
    'name' => esc_html__('Include in Course Listing', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'boolean',
    'default' => true,
    'required' => false,
    'desc' => esc_html__('Shows the course on the main course listing page when enabled.', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'lesson_title' => array(
    'name' => esc_html__('Lesson Title', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'boolean',
    'default' => true,
    'required' => false,
    'desc' => esc_html__('Display lesson titles when viewing a lesson (only applies to ReadyLaunch templates).', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'sales_url' => array(
    'name' => esc_html__('Sales Page URL', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => '',
    'required' => false,
    'desc' => esc_html__('Redirects to the sales page if this URL is set.', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'require_previous' => array(
    'name' => esc_html__('Require Previous Lesson/Quiz', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'boolean',
    'default' => false,
    'required' => false,
    'desc' => esc_html__('Require previous lesson/quiz to be completed before proceeding.', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'show_results' => array(
    'name' => esc_html__('Show Question Results', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'boolean',
    'default' => false,
    'required' => false,
    'desc' => esc_html__('Allows students to see whether their answers were correct or incorrect', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'show_answers' => array(
    'name' => esc_html__('Show Correct Answers', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'boolean',
    'default' => false,
    'required' => false,
    'desc' => esc_html__('Displays the correct answers after the quiz is completed.', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'accordion_course' => array(
    'name' => esc_html__('Show Accordion on Course Page', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'boolean',
    'default' => false,
    'required' => false,
    'desc' => esc_html__('Displays lessons in an accordion on the course page.', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'accordion_sidebar' => array(
    'name' => esc_html__('Show Accordion on Sidebar', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'boolean',
    'default' => false,
    'required' => false,
    'desc' => esc_html__('Displays lessons in an accordion on the lesson page sidebar.', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'certificates_enable' => array(
    'name' => esc_html__('Enable Certificates', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => 'disabled',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('Enable or disable certificates for this course.', 'memberpress-courses', 'memberpress-developer-tools'),
    'options' => array('enabled', 'disabled')
  ),

  'certificates_title' => array(
    'name' => esc_html__('Certificate Title', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => esc_html__('This certificate is awarded to', 'memberpress-courses', 'memberpress-developer-tools'),
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('The title text displayed at the top of the certificate.', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'certificates_footer_message' => array(
    'name' => esc_html__('Footer Message', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => esc_html__('Has successfully completed this course', 'memberpress-courses', 'memberpress-developer-tools'),
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('Message shown at the bottom of the certificate.', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'certificates_logo' => array(
    'name' => esc_html__('Certificate Logo', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => '',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('The logo image file that will appear on the certificate.', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'certificates_signature' => array(
    'name' => esc_html__('Certificate Signature', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => '',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('The signature image that will appear on the certificate.', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'certificates_instructor_name' => array(
    'name' => esc_html__('Instructor Name', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => esc_html__('John Smith', 'memberpress-courses', 'memberpress-developer-tools'),
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('The instructor’s name that appears on the certificate.', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'certificates_instructor_title' => array(
    'name' => esc_html__('Instructor Title', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => esc_html__('Director of Something', 'memberpress-courses', 'memberpress-developer-tools'),
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('The title of the instructor that appears on the certificate.', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'certificates_instructor_signature' => array(
    'name' => esc_html__('Instructor Signature', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => '',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('The instructor’s signature image that appears on the certificate.', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'certificates_bottom_logo' => array(
    'name' => esc_html__('Bottom Logo', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => '',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('The logo image that appears at the bottom of the certificate.', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'certificates_style' => array(
    'name' => esc_html__('Certificate Style', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => 'style_a',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('Choose the style of the certificate.', 'memberpress-courses', 'memberpress-developer-tools'),
    'options' => array('style_a', 'style_b', 'style_c')
  ),

  'certificates_force_download_pdf' => array(
    'name' => esc_html__('Force PDF Download', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => 'enabled',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('Whether to force the certificate to be downloaded as a PDF.', 'memberpress-courses', 'memberpress-developer-tools'),
    'options' => array('enabled', 'disabled')
  ),

  'certificates_text_color' => array(
    'name' => esc_html__('Text Color', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => '#3c3c3c',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('The color of the text on the certificate.', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'certificates_background_color' => array(
    'name' => esc_html__('Background Color', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => '',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('The background color of the certificate.', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'certificates_paper_size' => array(
    'name' => esc_html__('Paper Size', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => 'letter',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('The paper size for the certificate.', 'memberpress-courses', 'memberpress-developer-tools'),
    'options' => array('letter', 'a4')
  ),

  'certificates_expiration_date' => array(
    'name' => esc_html__('Expiration Date', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => 'disabled',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('Enable or disable an expiration date for the certificate.', 'memberpress-courses', 'memberpress-developer-tools'),
    'options' => array('enabled', 'disabled')
  ),

  'certificates_completion_date' => array(
    'name' => esc_html__('Completion Date', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => 'disabled',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('Enable or disable showing the completion date on the certificate.', 'memberpress-courses', 'memberpress-developer-tools'),
    'options' => array('enabled', 'disabled')
  ),

  'certificates_share_link' => array(
    'name' => esc_html__('Share Certificate Link', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => 'disabled',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('Allow sharing a link to the certificate online.', 'memberpress-courses', 'memberpress-developer-tools'),
    'options' => array('enabled', 'disabled')
  ),

  'certificates_expires_unit' => array(
    'name' => esc_html__('Expiration Unit', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => 'day',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('Specify the unit for the expiration duration (days, weeks, or months).', 'memberpress-courses', 'memberpress-developer-tools'),
    'options' => array('day', 'week', 'month')
  ),

  'certificates_expires_value' => array(
    'name' => esc_html__('Expiration Value', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => '1',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('The number of units after which the certificate will expire.', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'certificates_expires_reset' => array(
    'name' => esc_html__('Reset Expiration', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => 'disabled',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('Reset the certificate’s expiration date based on course retakes or updates.', 'memberpress-courses', 'memberpress-developer-tools'),
    'options' => array('enabled', 'disabled')
  ),

  'dripping' => array(
    'name' => esc_html__('Dripping Enabled', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => 'disabled',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('Enable or disable content dripping for this course.', 'memberpress-courses', 'memberpress-developer-tools'),
    'options' => array('enabled', 'disabled')
  ),

  'drip_type' => array(
    'name' => esc_html__('Drip Type', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => 'section',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('Specify if dripping applies to the entire course or sections.', 'memberpress-courses', 'memberpress-developer-tools'),
    'options' => array('course', 'section', 'lesson')
  ),

  'drip_amount' => array(
    'name' => esc_html__('Drip Amount', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => '1',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('The number of units (days, weeks, months) after which content should be dripped.', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'drip_time' => array(
    'name' => esc_html__('Drip Time', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => '12:00 AM',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('The specific time of day when the content becomes available.', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'drip_timezone' => array(
    'name' => esc_html__('Drip Timezone', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => 'UTC',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('The timezone to use for drip release times.', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'drip_frequency' => array(
    'name' => esc_html__('Drip Frequency', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => 'weekly',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('Frequency at which the course content is released: daily, weekly, or monthly.', 'memberpress-courses', 'memberpress-developer-tools'),
    'options' => array('daily', 'weekly', 'monthly')
  ),

  'drip_frequency_type' => array(
    'name' => esc_html__('Drip Frequency Type', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => 'course_start_date',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('Determines if content drips based on course start date or fixed date.', 'memberpress-courses', 'memberpress-developer-tools'),
    'options' => array('course_start_date', 'fixed_date')
  ),

  'drip_frequency_fixed_date' => array(
    'name' => esc_html__('Fixed Drip Date', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => '',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('If the drip frequency type is set to a fixed date, specify that date here.', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'drip_lessons' => array(
    'name' => esc_html__('Drip Lessons', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => '0',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('Number of lessons that will be dripped over time.', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'drip_quizzes' => array(
    'name' => esc_html__('Drip Quizzes', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => '0',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('Number of quizzes that will be dripped over time.', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'drip_assignments' => array(
    'name' => esc_html__('Drip Assignments', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => '0',
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('Number of assignments that will be dripped over time.', 'memberpress-courses', 'memberpress-developer-tools')
  ),

  'not_dripped_message' => array(
    'name' => esc_html__('Not Dripped Message', 'memberpress-courses', 'memberpress-developer-tools'),
    'type' => 'string',
    'default' => esc_html__('Curriculum is released every {mpcs_drip_schedule}. As such, this {mpcs_item_type} will not be available to you until {mpcs_drip_date}.', 'memberpress-courses', 'memberpress-developer-tools'),
    'required' => esc_html__('Optional', 'memberpress-courses', 'memberpress-developer-tools'),
    'desc' => esc_html__('The message shown to users when content is not yet dripped.', 'memberpress-courses', 'memberpress-developer-tools')
  )

);
