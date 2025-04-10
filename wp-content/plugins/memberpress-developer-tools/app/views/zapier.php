<?php if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');} ?>

<div class="mepr-page-title"><?php _e('Zapier', 'memberpress-developer-tools'); ?></div>

<p><?php _e('Seamlessly connect thousands of your favorite apps to your WordPress site. With the official Zapier + MemberPress integration, the possibilities are endless!', 'memberpress-developer-tools'); ?></p>

<script type="module" src="https://cdn.zapier.com/packages/partner-sdk/v0/zapier-elements/zapier-elements.esm.js"></script>
<link rel="stylesheet" href="https://cdn.zapier.com/packages/partner-sdk/v0/zapier-elements/zapier-elements.css"/>

<zapier-zap-templates
  theme="light"
  apps="memberpress"
></zapier-zap-templates>