<?php

register_activation_hook(WP2PCS_PLUGIN_NAME,'wp2pcs_install');
function wp2pcs_install(){
  $run_time = strtotime(date('Y-m-d 01:00:00',strtotime('+7 day')));
  wp_schedule_event($run_time,'weekly','wp2pcs_token_cron_task');
  add_option('wp2pcs_do_activation_redirect',true);
}

add_action('admin_init','wp2pcs_install_redirect');
function wp2pcs_install_redirect() {
  if(get_option('wp_to_pcs_app_key')) { // 如果存在这个值，说明是从老版本升级过来的
    add_option('wp2pcs_do_activation_redirect',true);
    delete_option('wp_to_pcs_app_key');
  }
  if(get_option('wp2pcs_do_activation_redirect')) {
    delete_option('wp2pcs_do_activation_redirect');
    wp_redirect(admin_url('plugins.php?page=wp2pcs&tab=about'));
  }
}
