<?php

register_activation_hook(WP2PCS_PLUGIN_NAME,'wp2pcs_install');
function wp2pcs_install(){
  $run_time = strtotime(date('Y-m-d 01:00:00',strtotime('+7 day')));
  wp_schedule_event($run_time,'weekly','wp2pcs_token_cron_task');
  add_option('wp2pcs_do_activation_redirect',true);
}

add_action('admin_init','wp2pcs_install_redirect');
function wp2pcs_install_redirect() {
  if(get_option('wp2pcs_do_activation_redirect',false)) {
    delete_option('wp2pcs_do_activation_redirect');
    wp_redirect(admin_url('plugins.php?page=wp2pcs&tab=about'));
  }
}