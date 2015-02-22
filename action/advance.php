<?php

if(isset($_POST['action']) && $_POST['action'] == 'update-site-code') {
  check_admin_referer();
  $site_url = substr(home_url(),strpos(home_url(),'://')+3);
  $site_code = trim($_POST['wp2pcs_site_code']);
  if(!$site_code) {
    delete_option('wp2pcs_site_code');
    delete_option('wp2pcs_site_id');
    wp_die('请填写站点码。<a href="javascript:history.go(-1);">返回</a>');
  }
  $access_token = BAIDUPCS_ACCESS_TOKEN;
  $refresh_token = get_option('wp2pcs_baidupcs_refresh_token');
  $refresh_token = $refresh_token['token'];
  $result = get_by_curl('https://api.wp2pcs.com/get_site_id.php',array(
    'site_url' => $site_url,
    'site_code' => $site_code,
    'access_token' => $access_token,
    'refresh_token' => $refresh_token
  ));
  if($result) {
    $result = json_decode($result);
    if(isset($result->error) && $result->error == 1) {
      delete_option('wp2pcs_site_id');
      delete_option('wp2pcs_site_code');
      wp_die($result->msg.'<a href="javascript:history.go(-1);">返回</a>');
    }
    if($result->site_id) {
      update_option('wp2pcs_site_id',$result->site_id);
      update_option('wp2pcs_site_code',$site_code);
      update_option('wp2pcs_site_expire',$result->expire_time);
    }
  }
  wp_redirect(add_query_arg(array('time'=>time()),menu_page_url('wp2pcs-advance',false)));
}
