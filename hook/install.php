<?php

register_activation_hook(WP2PCS_PLUGIN_NAME,'wp2pcs_install');
function wp2pcs_install(){
  wp_schedule_event(strtotime('+7 days'),'weekly','wp2pcs_token_cron_task');
}

add_action('admin_init','wp2pcs_install_redirect');
function wp2pcs_install_redirect() {
  $plugin_version = get_option('wp2pcs_plugin_version');
  if($plugin_version < WP2PCS_PLUGIN_VERSION) {
    update_option('wp2pcs_plugin_version',WP2PCS_PLUGIN_VERSION);
    wp_redirect(add_query_arg(array('tab'=>'about','time'=>time()),menu_page_url('wp2pcs-setting',false)));
  }
}
