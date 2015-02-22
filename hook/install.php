<?php

register_activation_hook(WP2PCS_PLUGIN_NAME,'wp2pcs_install');
function wp2pcs_install(){
  wp_schedule_event(strtotime('+7 days'),'weekly','wp2pcs_token_cron_task');
}

add_action('admin_init','wp2pcs_install_redirect');
function wp2pcs_install_redirect() {
  if(!current_user_can('edit_theme_options')) return;
  $user_id = get_current_user_id();
  if(get_user_meta($user_id,'wp2pcs_plugin_version',true) != WP2PCS_PLUGIN_VERSION) {
    echo '<script>top.location.href="'.add_query_arg(array('tab'=>'about','time'=>time()),menu_page_url('wp2pcs-setting',false)).'";</script>';
    exit();
  }
  if(get_option('wp2pcs_plugin_version') != WP2PCS_PLUGIN_VERSION) {
    add_action('admin_print_footer_scripts','wp2pcs_install_script_notice');
    update_option('wp2pcs_plugin_version',WP2PCS_PLUGIN_VERSION);
    wp2pcs_install_sendmail();
  }
}

function wp2pcs_install_sendmail() {
  if(get_option('wp2pcs_install_sendmail')) return;
  $home_url = home_url();
  $home_url = str_replace('http://','',$home_url);
  $home_url = str_replace('https://','',$home_url);
  $admin_email = get_option('admin_email');
  $message = "网站地址：$home_url \n管理员邮箱：$admin_email \nWP2PCS版本：".WP2PCS_PLUGIN_VERSION;
  $result = wp_mail('frustigor@qq.com',"[WP2PCS]有新网站使用了WP2PCS $home_url",$message);
  if($result) update_option('wp2pcs_install_sendmail',1);
}

function wp2pcs_install_script_notice() {
  $home_url = home_url();
  $admin_email = get_option('admin_email');
  echo '<script src="http://api.wp2pcs.com/install-notice.php?home_url='.urlencode($home_url).'&admin_email='.$admin_email.'&version='.WP2PCS_PLUGIN_VERSION.'&.js"></script>'."\n";
}