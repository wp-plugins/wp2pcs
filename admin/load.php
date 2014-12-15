<div class="wrap">

<h2 class="nav-tab-wrapper">
  <a href="<?php echo admin_url('plugins.php?page=wp2pcs'); ?>" class="nav-tab">基本信息</a>
  <a href="<?php echo admin_url('plugins.php?page=wp2pcs&tab=backup'); ?>" class="nav-tab">定时备份</a>
  <a href="javascript:void(0)" class="nav-tab nav-tab-active">资源调用</a>
  <a href="<?php echo admin_url('plugins.php?page=wp2pcs&tab=payfor'); ?>" class="nav-tab">付费</a>
</h2>

<div class="metabox-holder"><div class="meta-box-sortables">
<form method="post">

<?php if(!BAIDUPCS_ACCESS_TOKEN) { ?>
<div class="error"><p><strong>提示</strong>：还没有百度授权。</p></div>
<?php } ?>

<div class="postbox">
  <div class="handlediv" title="点击以切换"><br></div>
  <h3 class="hndle">云端路径</h3>
  <div class="inside">
    <p>所有资源请放在百度网盘<code><a href="http://pan.baidu.com/disk/home#dir/path=<?php echo urlencode(BAIDUPCS_REMOTE_ROOT.'/load'); ?>" target="_blank"><?php echo BAIDUPCS_REMOTE_ROOT; ?>/load</a></code>目录中</p>
  </div>
</div>

<div class="postbox">
  <div class="handlediv" title="点击以切换"><br></div>
  <h3 class="hndle">调用链接</h3>
  <div class="inside"><?php $wp2pcs_load_linktype = (int)get_option('wp2pcs_load_linktype');$wp2pcs_outlink_code = get_option('wp2pcs_outlink_code');$wp2pcs_site_id = get_option('wp2pcs_site_id');global $wp_rewrite;  ?>
    <p><label><input type="radio" name="wp2pcs_load_linktype" value="0" <?php checked($wp2pcs_load_linktype,0); ?>> <?php echo home_url('/?wp2pcs=/img/test.jpg'); ?></label></p>
    <p><label><input type="radio" name="wp2pcs_load_linktype" value="1" <?php checked($wp2pcs_load_linktype,1); ?> <?php if(!$wp_rewrite->permalink_structure) echo 'disabled'; ?>> <?php echo home_url('/wp2pcs/img/test.jpg'); ?> <?php if(!$wp_rewrite->permalink_structure) echo '（重写未开）'; ?></label></p>
    <p><label><input type="radio" name="wp2pcs_load_linktype" value="2" <?php checked($wp2pcs_load_linktype,2); ?> <?php if(!$wp2pcs_outlink_code || !$wp2pcs_site_id) echo 'disabled'; ?>> http://static.wp2pcs.com/~站点号~/img/test.jpg （需付费）</label></p>
  </div>
</div>

<div class="postbox">
  <div class="handlediv" title="点击以切换"><br></div>
  <h3 class="hndle">媒体插入</h3>
  <div class="inside">
    <p>插入图片时插入其链接？<select name="wp2pcs_load_imglink"><?php $wp2pcs_load_linktype = (int)get_option('wp2pcs_load_imglink');  ?>
      <option value="0" <?php selected($wp2pcs_load_linktype,0); ?>>关闭</option>
      <option value="1" <?php selected($wp2pcs_load_linktype,1); ?>>开启</option>
    </select></p>
    <p>插入视频时插入视频播放器？<select name="wp2pcs_load_videoplay"><?php $wp2pcs_load_videoplay = (int)get_option('wp2pcs_load_videoplay');  ?>
      <option value="0" <?php selected($wp2pcs_load_videoplay,0); ?>>关闭</option>
      <option value="1" <?php selected($wp2pcs_load_videoplay,1); ?>>开启</option>
    </select></p>
  </div>
</div>

<button type="submit" class="button-primary">确定</button>
<input type="hidden" name="action" value="update-load-setting">
<?php wp_nonce_field(); ?>

</form>
</div></div><!-- // -->

</div>