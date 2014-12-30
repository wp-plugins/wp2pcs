<div class="wrap">

<h2 class="nav-tab-wrapper">
  <a href="<?php menu_page_url('wp2pcs-advance'); ?>" class="nav-tab">站点</a>
  <a href="<?php echo add_query_arg('tab','outlink',menu_page_url('wp2pcs-advance',false)); ?>" class="nav-tab">外链</a>
  <a href="javascript:void(0)" class="nav-tab nav-tab-active">视频</a>
</h2>

<div class="metabox-holder"><div class="meta-box-sortables">
<form method="post" autocomplete="off">

<?php include('tpl/advance-setup.php'); ?>
<?php if(!$wp2pcs_site_code || !$wp2pcs_site_id) update_option('wp2pcs_video_m3u8',0); ?>
<?php include('tpl/advance-site-info.php'); ?>

<div class="postbox">
  <div class="handlediv" title="点击以切换"><br></div>
  <h3 class="hndle">m3u8视频服务</h3>
  <div class="inside">
    <p>采用m3u8流视频？<select name="wp2pcs_video_m3u8"><?php $wp2pcs_video_m3u8 = (int)get_option('wp2pcs_video_m3u8');  ?>
        <option value="0" <?php selected($wp2pcs_video_m3u8,0); ?>>关闭</option>
        <option value="1" <?php selected($wp2pcs_video_m3u8,1);if(!$wp2pcs_site_code || !$wp2pcs_site_id || time() > $wp2pcs_vip_expire) echo ' disabled'; ?>>开启</option>
      </select>
    </p>
    <p><small>关闭后，之前使用m3u8播放的视频将采用免费模式播放。开启后，自动启用“插入视频时插入视频播放器”功能。</small></p>
  </div>
</div>

<p><button type="submit" class="button-primary">确定</button></p>
<input type="hidden" name="action" value="update-video-setting">
<?php wp_nonce_field(); ?>
</form>
</div></div><!-- // -->

</div>
