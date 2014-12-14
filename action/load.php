<?php

if(isset($_POST['action']) && $_POST['action'] == 'update-load-setting') {
  check_admin_referer();
  update_option('wp2pcs_load_linktype',$_POST['wp2pcs_load_linktype']);
  wp_redirect(admin_url('plugins.php?page=wp2pcs&tab=load&time='.time()));
}
