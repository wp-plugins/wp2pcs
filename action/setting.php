<?php

if(isset($_GET['access_token']) && !empty($_GET['access_token'])) {
  $access_token = $_GET['access_token'];
  if(isset($_GET['oauth']) && $_GET['oauth'] == 'baidupcs' && isset($_GET['refresh_token']) && !empty($_GET['refresh_token'])) {
    $refresh_token = array(
      'time' => time(),
      'token' => $_GET['refresh_token']
    );
    update_option('wp2pcs_baidupcs_access_token',$access_token);
    update_option('wp2pcs_baidupcs_refresh_token',$refresh_token);
  }
  elseif(isset($_GET['oauth']) && $_GET['oauth'] == 'weiyun' && isset($_GET['open_id']) && !empty($_GET['open_id'])) {
    update_option('wp2pcs_tencent_access_token',$access_token);
    update_option('wp2pcs_tencent_open_id',$_GET['open_id']);
    update_option('wp2pcs_tencent_app_id','101161347');
  }
  wp_redirect(menu_page_url('wp2pcs-setting',false));
}

