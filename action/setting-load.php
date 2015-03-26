<?php

// 更新配置
if(isset($_POST['action']) && $_POST['action'] == 'update-load-setting') {
  check_admin_referer();
  // 更新链接
  $linktype = (int)$_POST['wp2pcs_load_linktype'];
  if($linktype == 2) {
    $wp2pcs_site_id = get_option('wp2pcs_site_id');
    $wp2pcs_site_expire = get_option('wp2pcs_site_expire');
    if(!$wp2pcs_site_id || time() > $wp2pcs_site_expire) {
      $linktype = 1;
    }
  }
  if($linktype == 1) {
    global $wp_rewrite;
    if(!$wp_rewrite->permalink_structure) {
      $linktype = 0;
    }
  }
  update_option('wp2pcs_load_linktype',$linktype);
  // 更新是否插入图片链接
  update_option('wp2pcs_load_imglink',$_POST['wp2pcs_load_imglink']);
  // 更新是否缓存
  update_option('wp2pcs_load_cache',$_POST['wp2pcs_load_cache']);
  // 更新水印
  update_option('wp2pcs_image_watermark',realpath(ABSPATH).trim($_POST['wp2pcs_image_watermark']));
  wp_redirect(add_query_arg(array('tab'=>'load','time'=>time()),menu_page_url('wp2pcs-setting',false)));
}
// 清空缓存
elseif(isset($_GET['action']) && $_GET['action'] == 'clean-cache') {
  check_admin_referer();
  wp2pcs_clean_cache();
  wp_redirect(add_query_arg(array('tab'=>'load','time'=>time()),menu_page_url('wp2pcs-setting',false)));
}
