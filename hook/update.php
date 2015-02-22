<?php

// WP2PCS升级后可能存在的一些变化，通过本文件进行调整

// 跳转到升级介绍
if(get_user_meta(get_current_user_id(),'wp2pcs_plugin_version',true) != WP2PCS_PLUGIN_VERSION) {
  if(get_url_file_name() == 'plugins.php' && $_GET['action'] == 'activate') return;// 激活插件时不执行
  if(get_url_file_name() == 'update.php' && $_GET['action'] == 'upgrade-plugin') return;
  update_user_meta(get_current_user_id(),'wp2pcs_plugin_version',WP2PCS_PLUGIN_VERSION);
  wp_redirect(add_query_arg(array('tab'=>'about','time'=>time()),menu_page_url('wp2pcs-setting',false)));
  exit();
}

// 免费版关闭视频播放器功能
if(!get_option('wp2pcs_site_id')) {
  update_option('wp2pcs_video_m3u8',0);
  update_option('wp2pcs_load_videoplay',0);
}

// 会员过期改为站点过期
$wp2pcs_site_expire = get_option('wp2pcs_site_expire');
$wp2pcs_vip_expire = get_option('wp2pcs_vip_expire');
if(!$wp2pcs_site_expire && $wp2pcs_vip_expire) {
  update_option('wp2pcs_site_expire',$wp2pcs_vip_expire);
  delete_option('wp2pcs_vip_expire');
}

// wp2pcs_load_videoplay 改为 wp2pcs_video_player
$wp2pcs_video_player = get_option('wp2pcs_video_player');
$wp2pcs_load_videoplay = get_option('wp2pcs_load_videoplay');
if(!$wp2pcs_video_player && $wp2pcs_load_videoplay) {
  update_option('wp2pcs_video_player',$wp2pcs_load_videoplay);
  delete_option('wp2pcs_load_videoplay');
}