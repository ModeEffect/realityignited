<?php
if(!defined('ABSPATH')) { die('You are not allowed to call this page directly.'); }
?>
<div class="wrap">
  <h2><?php esc_html_e('File Stats', 'memberpress-downloads'); ?></h2>
  <?php $list_table->display(); ?>
</div>
