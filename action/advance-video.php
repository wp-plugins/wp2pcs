<?php

if(isset($_POST['action']) && $_POST['action'] == 'update-video-setting') {
  check_admin_referer();
  $video_m3u8 = (int)$_POST['wp2pcs_video_m3u8'];
  if($video_m3u8 == 1) {
    $wp2pcs_site_code = get_option('wp2pcs_site_code');
    $wp2pcs_site_id = (int)get_option('wp2pcs_site_id');
    $wp2pcs_vip_expire = (int)get_option('wp2pcs_vip_expire');
    if(!$wp2pcs_site_code || !$wp2pcs_site_id || time() > $wp2pcs_vip_expire) {
      $video_m3u8 = 0;
    }
    else {
      update_option('wp2pcs_load_videoplay',1);
    }
  }
  update_option('wp2pcs_video_m3u8',$video_m3u8);
  wp_redirect(add_query_arg(array('tab'=>'video','time'=>time()),menu_page_url('wp2pcs-advance',false)));
}
