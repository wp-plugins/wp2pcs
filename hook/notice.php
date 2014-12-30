<?php

add_action('admin_print_footer_scripts','wp2pcs_admin_notice',99);
function wp2pcs_admin_notice() {
  if(!current_user_can('edit_theme_options')) return;
  $current_php_file = substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'],'/')+1);
  if(in_array($current_php_file,array('post.php','post-new.php','media-upload.php'))){
    return;
  }
  $wp2pcs_admin_notice = (int)get_option('wp2pcs_admin_notice');
  if($wp2pcs_admin_notice < strtotime(date('Y-m-d 00:00:00'))) {
    echo '<script src="//static.wp2pcs.com/admin-notice.php?time='.$wp2pcs_admin_notice.'&code='.wp_create_nonce().'&.js" id="wp2pcs-admin-notice"></script>';
  }
}

add_action('admin_init','wp2pcs_admin_notice_update'); 
function wp2pcs_admin_notice_update() {
  if(!current_user_can('edit_theme_options')) return;
  if(isset($_GET['action']) && $_GET['action'] == 'wp2pcs-admin-notice-update') {
    check_admin_referer();
    update_option('wp2pcs_admin_notice',time());
    exit;
  }
}
