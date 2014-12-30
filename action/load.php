<?php

if(isset($_POST['action']) && $_POST['action'] == 'update-load-setting') {
  check_admin_referer();
  update_option('wp2pcs_load_linktype',$_POST['wp2pcs_load_linktype']);
  update_option('wp2pcs_load_imglink',$_POST['wp2pcs_load_imglink']);
  update_option('wp2pcs_load_videoplay',$_POST['wp2pcs_load_videoplay']);
  update_option('wp2pcs_load_videom3u8',$_POST['wp2pcs_load_videom3u8']);
  update_option('wp2pcs_load_cache',$_POST['wp2pcs_load_cache']);
  wp_redirect(admin_url('plugins.php?page=wp2pcs&tab=load&time='.time()));
}
// 立即备份
elseif(isset($_GET['action']) && $_GET['action'] == 'clean-cache') {
  check_admin_referer();
  wp2pcs_clean_cache();// 清空缓存
  wp_redirect(admin_url('plugins.php?page=wp2pcs&tab=load&time='.time()));
}
