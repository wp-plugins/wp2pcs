<?php
$wp2pcs_site_code = get_option('wp2pcs_site_code');
$wp2pcs_site_id = (int)get_option('wp2pcs_site_id');
$wp2pcs_vip_expire = (int)get_option('wp2pcs_vip_expire');
$wp2pcs_load_linktype = (int)get_option('wp2pcs_load_linktype');
global $wp_rewrite;
?>
<div class="postbox">
  <div class="handlediv" title="点击以切换"><br></div>
  <h3 class="hndle">调用链接</h3>
  <div class="inside">
    <p><label><input type="radio" name="wp2pcs_load_linktype" value="0" <?php checked($wp2pcs_load_linktype,0); ?>> <?php echo home_url('/?wp2pcs=/img/test.jpg'); ?></label></p>
    <p><label><input type="radio" name="wp2pcs_load_linktype" value="1" <?php checked($wp2pcs_load_linktype,1); ?> <?php if(!$wp_rewrite->permalink_structure) echo 'disabled'; ?>> <?php echo home_url('/wp2pcs/img/test.jpg'); ?> <?php if(!$wp_rewrite->permalink_structure) echo '（重写未开）'; ?></label></p>
    <p><label><input type="radio" name="wp2pcs_load_linktype" value="2" <?php checked($wp2pcs_load_linktype,2); ?> <?php if(!$wp2pcs_site_code || !$wp2pcs_site_id || time() > $wp2pcs_vip_expire) echo 'disabled'; ?>> http://static.wp2pcs.com/<?php if($wp2pcs_site_id) echo $wp2pcs_site_id; else echo '~站点号~'; ?>/img/test.jpg （付费用户专享）</label></p>
    <p><small>更换形式后，以前的仍然有效。</small></p>
  </div>
</div>
