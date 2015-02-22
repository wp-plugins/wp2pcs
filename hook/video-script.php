<?php

// 判断如果加载了WordPress
if(defined('ABSPATH')) {

// 前台访问打印
add_action('wp_footer','wp2pcs_video_player_script',0);
function wp2pcs_video_player_script() {
$site_id = get_option('wp2pcs_site_id');
if($site_id && get_option('wp2pcs_video_m3u8')) :
?>
<style>
<?php
echo '.wp2pcs-video-player{display:block;margin:1em auto;cursor:pointer;background:url('.plugins_url('assets/video-play.png',WP2PCS_PLUGIN_NAME).') no-repeat center #f5f5f5;-moz-opacity:0.6;opacity:0.6;overflow:hidden;border:0;}';
echo '.wp2pcs-video-player a{display:block;min-width:480px;min-height:360px;margin: auto;}';
echo '.wp2pcs-video-player:hover{-moz-opacity:1;opacity:1;}';
echo '.wp2pcs-video-player img{-moz-opacity:0.6;opacity:0.6;width:100%;height:100%;}';
echo '.wp2pcs-video-playing{display:block;border:0;margin:auto;background:url('.plugins_url('assets/loading.gif',WP2PCS_PLUGIN_NAME).') no-repeat center;}';
?>
</style>
<script>window.jQuery || document.write('<script type="text/javascript" src="<?php echo plugins_url("assets/jquery-1.11.2.min.js",WP2PCS_PLUGIN_NAME); ?>">\x3C/script>');</script>
<script type="text/javascript">
<?php
echo 'function wp2pcs_setup_videos() {';
echo 'jQuery("iframe.wp2pcs-video-player").each(function(){';
echo 'var $this = jQuery(this),';
echo 'path = $this.attr("data-path"),';
echo 'width = $this.attr("width"),';
echo 'height = $this.attr("height"),';
echo 'stretch = $this.attr("data-stretch"),';
echo 'md5 = $this.attr("data-md5"),';
echo 'root_dir = $this.attr("data-root-dir"),';
echo 'image = $this.attr("data-image");';
echo 'if(root_dir != undefined) {';
echo 'if(root_dir == "share") root_dir = "/apps/wp2pcs/share";';
echo '}';
echo 'else {';
echo 'root_dir = "'.BAIDUPCS_REMOTE_ROOT.'/load";';
echo '}';
echo 'if(path.indexOf(root_dir) != 0) path = root_dir + path;';
echo '$this.attr("src","http://static.wp2pcs.com/player?site_id='.$site_id.'&size=" + width + "_" + height + "&stretch=" + stretch + "&image=" + image + "&md5=" + md5 + "&path=" + path);';
echo '$this.removeClass("wp2pcs-video-player").addClass("wp2pcs-video-playing");';
echo '$this.attr("frameborder","0");';
echo '$this.attr("scrolling","no");';
echo '});';
echo '}';
echo 'wp2pcs_setup_videos();';
?>
</script>
<?php
endif;
}

// 加入到后台编辑器中css
add_action('init','wp2pcs_admin_editor_video_player_style');
function wp2pcs_admin_editor_video_player_style() {
  add_editor_style(plugins_url('hook/video-script.php?script=style.css',WP2PCS_PLUGIN_NAME));
}


}
// 如果没有加载WordPress的话
else {

// 直接访问文件的时候打印CSS
if(isset($_GET['script']) && $_GET['script'] == 'style.css') {
  header('Content-Type: text/css; charset=utf-8');
  echo '.wp2pcs-video-player{display:block;margin:auto !important;}';
  echo '.wp2pcs-video-player:hover{}';
  exit;
}

} // 没有加载Wordpress结束