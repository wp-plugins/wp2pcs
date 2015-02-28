<div class="wrap">

<h2 class="nav-tab-wrapper">
  <a href="<?php menu_page_url('wp2pcs-advance'); ?>" class="nav-tab">站点</a>
  <a href="<?php echo add_query_arg('tab','outlink',menu_page_url('wp2pcs-advance',false)); ?>" class="nav-tab">外链</a>
  <a href="javascript:void(0)" class="nav-tab nav-tab-active">视频</a>
</h2>

<div class="metabox-holder"><div class="meta-box-sortables">
<form method="post" autocomplete="off">

<?php
include('tpl/advance-setup.php');
if(!$wp2pcs_site_id || time() > $wp2pcs_site_expire) {
  update_option('wp2pcs_video_m3u8',0);
  update_option('wp2pcs_video_player',0);
}
include('tpl/advance-site-info.php');
?>

<?php 
$wp2pcs_video_m3u8 = (int)get_option('wp2pcs_video_m3u8');
$wp2pcs_video_player = (int)get_option('wp2pcs_video_player');
?>
<div class="postbox">
  <div class="handlediv" title="点击以切换"><br></div>
  <h3 class="hndle">m3u8视频服务</h3>
  <div class="inside">
    <p>开启m3u8视频服务？<select name="wp2pcs_video_m3u8">
        <option value="0" <?php selected($wp2pcs_video_m3u8,0); ?>>关闭</option>
        <option value="1" <?php if($wp2pcs_site_id && time() < $wp2pcs_site_expire) selected($wp2pcs_video_m3u8,1); else echo ' disabled'; ?>>开启</option>
      </select>
    </p>
    <p><small>关闭后，之前使用m3u8播放的视频将采用免费模式播放。开启后，自动启用“插入视频时插入视频播放器”功能。</small></p>
    <p>插入视频时插入视频播放器？<select name="wp2pcs_video_player">
        <option value="0" <?php selected($wp2pcs_video_player,0); ?>>关闭</option>
        <option value="1" <?php if($wp2pcs_site_id && time() < $wp2pcs_site_expire) selected($wp2pcs_video_player,1); else echo ' disabled'; ?>>开启</option>
      </select>
      <small>免费版的视频播放功能已经不能使用了</small>
    </p>
  </div>
</div>

<p><button type="submit" class="button-primary">确定</button></p>
<input type="hidden" name="action" value="update-video-setting">
<?php wp_nonce_field(); ?>
</form>
</div></div><!-- // -->

</div>