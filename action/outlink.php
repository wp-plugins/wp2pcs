<?php

if(isset($_POST['action']) && $_POST['action'] == 'update-outlink-setting') {
  check_admin_referer();
  $site_url = substr(home_url(),strpos(home_url(),'://')+3);
  $outlink_code = trim($_POST['wp2pcs_outlink_code']);
  $access_token = BAIDUPCS_ACCESS_TOKEN;
  update_option('wp2pcs_outlink_code',$outlink_code);
  $result = get_by_curl('https://api.wp2pcs.com/get_site_id.php',array(
    'site_url' => $site_url,
    'outlink_code' => $outlink_code,
    'access_token' => $access_token
  ));
  if($result) {
    $result = json_decode($result);
    if(isset($result->error) && $result->error == 1) wp_die($result->msg);
    if(isset($result->site_id)) update_option('wp2pcs_site_id',$result->site_id);
  }
  wp_redirect(admin_url('plugins.php?page=wp2pcs&tab=outlink&time='.time()));
}

if(!function_exists('get_by_curl')) :
function get_by_curl($url,$post = false){
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	if($post){
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
	}
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}
endif;
