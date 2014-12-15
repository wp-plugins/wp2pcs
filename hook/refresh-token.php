<?php

add_action('wp2pcs_token_cron_task','wp2pcs_refresh_baidupcs_token');
function wp2pcs_refresh_baidupcs_token() {
  $wp2pcs_baidupcs_refresh_token = get_option('wp2pcs_baidupcs_refresh_token');
  if(time() > $wp2pcs_baidupcs_refresh_token['time'] + 3600*24*27) {
    $post = array(
      'refresh_token' => $wp2pcs_baidupcs_refresh_token['token']
    );
    $data = get_by_curl('https://api.wp2pcs.com/oauth_baidupcs_refresh_token.php',$post);
    $data = json_decode($data);
    if(isset($data->access_token) && isset($data->refresh_token)) {
      $access_token = $data->access_token;
      $refresh_token = array(
        'time' => time(),
        'token' => $data->refresh_token
      );
      update_option('wp2pcs_baidupcs_access_token',$access_token);
      update_option('wp2pcs_baidupcs_refresh_token',$refresh_token);
      $outlink_code = get_option('wp2pcs_outlink_code');
      if($outlink_code) {
        $site_url = substr(home_url(),strpos(home_url(),'://')+3);
        $result = get_by_curl('https://api.wp2pcs.com/get_site_id.php',array(
          'site_url' => $site_url,
          'outlink_code' => $outlink_code,
          'access_token' => $access_token
        ));
        if($result) {
          $result = json_decode($result);
          if(isset($result->site_id))update_option('wp2pcs_site_id');
        }
      }
    }
  }
}

add_action('wp_footer','wp2pcs_footer_copyright');
function wp2pcs_footer_copyright() {
  echo '<!-- 本站由WP2PCS驱动，自动备份网站到云盘，调用云盘资源 http://www.wp2pcs.com -->'."\n";
}
