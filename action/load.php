<?php

if(isset($_POST['action']) && $_POST['action'] == 'update-load-setting') {
  check_admin_referer();
  update_option('wp2pcs_load_linktype',$_POST['wp2pcs_load_linktype']);
  update_option('wp2pcs_load_imglink',$_POST['wp2pcs_load_imglink']);
  update_option('wp2pcs_load_videoplay',$_POST['wp2pcs_load_videoplay']);
  wp_redirect(admin_url('plugins.php?page=wp2pcs&tab=load&time='.time()));
}
