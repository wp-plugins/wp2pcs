<?php

if(isset($_POST['action']) && $_POST['action'] == 'update-video-setting') {
  check_admin_referer();
  update_option('wp2pcs_video_m3u8',$_POST['wp2pcs_video_m3u8']);
  if($_POST['wp2pcs_video_m3u8']) {
    update_option('wp2pcs_video_player',$_POST['wp2pcs_video_player']);
  }
  else {
    update_option('wp2pcs_video_player',0);
  }
  wp_redirect(add_query_arg(array('tab'=>'video','time'=>time()),menu_page_url('wp2pcs-advance',false)));
}