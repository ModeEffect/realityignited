<span class="filter-by">
  <label><?php esc_html_e('Filter by', 'memberpress-downloads'); ?></label>

  <input
    type="text"
    name="mepr_access_row[condition][]"
    class="mpdl_suggest_files mepr_filter_field mepr-rule-access-condition-input"
    placeholder="<?php esc_attr_e('File Name', 'memberpress-downloads'); ?>"
    id="file_name"
    value="<?php echo esc_attr($file_name) ?>"
  >

  <input
    type="text"
    name="mepr_access_row[condition][]"
    class="mepr_filter_field datepicker mepr-rule-access-condition-input"
    placeholder="<?php esc_attr_e('Start Date', 'memberpress-downloads'); ?>"
    id="start_date"
    value="<?php echo esc_attr($start_date) ?>"
  >

  <input
    type="text"
    name="mepr_access_row[condition][]"
    class="mepr_filter_field datepicker mepr-rule-access-condition-input"
    placeholder="<?php esc_attr_e('End Date', 'memberpress-downloads'); ?>"
    id="end_date"
    value="<?php echo esc_attr($end_date) ?>"
  >

  <input type="submit" id="mepr_search_filter" class="button" value="<?php esc_attr_e('Go', 'memberpress-downloads'); ?>" />

  <?php if(isset($_REQUEST['file_name']) || isset($_REQUEST['start_date']) || isset($_REQUEST['end_date'])) : ?>
    <a href="<?php echo esc_url(remove_query_arg(['file_name', 'start_date', 'end_date'])); ?>">[x]</a>
  <?php endif; ?>
</span>
