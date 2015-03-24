<?php

add_action('wp2pcs_token_cron_task','wp2pcs_refresh_baidupcs_token');
function wp2pcs_refresh_baidupcs_token() {
  $site_url = substr(home_url(),strpos(home_url(),'://')+3);
  $access_token = get_option('wp2pcs_baidupcs_access_token');
  $refresh_token = get_option('wp2pcs_baidupcs_refresh_token');
  $site_expire = get_option('wp2pcs_site_expire');
  $site_code = get_option('wp2pcs_site_code');
  $site_id = get_option('wp2pcs_site_id');
  // 如果refresh_token是一个月以前的，那么就更新之
  if(time() > $refresh_token['time'] + 3600*24*28) {
    $post_data = array(
      'site_url' => $site_url,
      'refresh_token' => $refresh_token['token']
    );
    if($site_id && $site_code) {
      $post_data['site_id'] = $site_id;
      $post_data['code'] = md5($site_code);
    }
    $data = get_by_curl('https://api.wp2pcs.com/oauth_baidupcs_refresh_token.php',$post_data);
    $data = json_decode($data);
    if($data->access_token && $data->refresh_token) {
      $access_token = $data->access_token;
      $refresh_token = array(
        'time' => time(),
        'token' => $data->refresh_token
      );
      update_option('wp2pcs_baidupcs_access_token',$access_token);
      update_option('wp2pcs_baidupcs_refresh_token',$refresh_token);
    }
  }
  // 如果会员已经过期了
  if($site_code && $site_id) {
    $data = get_by_curl('https://api.wp2pcs.com/get_site_id.php',array(
      'site_url' => $site_url,
      'site_code' => $site_code,
      'access_token' => $access_token,
      'refresh_token' => $refresh_token['token']
    ));
    if($data) {
      $data = json_decode($data);
      if($data->site_id && $data->expire_time > time()) {
        update_option('wp2pcs_site_id',$data->site_id);
        update_option('wp2pcs_site_expire',$data->expire_time);
      }
      if($site_expire > $data->expire_time) {
        delete_option('wp2pcs_site_id');
        delete_option('wp2pcs_site_code');
        delete_option('wp2pcs_site_expire');
      }
    }
  }
}

add_action('wp_footer','wp2pcs_footer_copyright',-10);
function wp2pcs_footer_copyright() {
  echo '<!-- 本站由WP2PCS驱动，自动备份网站到云盘，调用云盘资源 http://www.wp2pcs.com -->'."\n";
}
