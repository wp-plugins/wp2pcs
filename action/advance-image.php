<?php

if(isset($_POST['action']) && $_POST['action'] == 'update-image-setting') {
  check_admin_referer();
  // 更新链接
  $linktype = (int)$_POST['wp2pcs_load_linktype'];
  if($linktype == 2) {
    $wp2pcs_site_code = get_option('wp2pcs_site_code');
    $wp2pcs_site_id = (int)get_option('wp2pcs_site_id');
    $wp2pcs_site_expire = (int)get_option('wp2pcs_site_expire');
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
  // 更新水印
  $wp2pcs_image_watermark = trim(@$_POST['wp2pcs_image_watermark']);
  if($wp2pcs_image_watermark) {
    update_option('wp2pcs_image_watermark',realpath(ABSPATH).$wp2pcs_image_watermark);
  }
  else {
    delete_option('wp2pcs_image_watermark');
  }
  wp_redirect(add_query_arg(array('tab'=>'image','time'=>time()),menu_page_url('wp2pcs-advance',false)));
}