<?php

function wp2pcs_video_player_css_in_admin_editor() {
?>
.wp2pcs-video-player {display:block;width:480px;height:360px;margin: 1em auto;}
.wp2pcs-video-player a {display:block;width:100%;height:100%;border:#dedede dashed 5px;background:url(../assets/video-play.png) no-repeat center;}
.wp2pcs-video-player iframe {display:block;width:100%;height:100%;}
<?php
}

// 直接访问文件的时候打印
if(isset($_GET['script']) && $_GET['script'] == 'style.css') {
  header('Content-Type: text/css; charset=utf-8');
  wp2pcs_video_player_css_in_admin_editor();
  exit;
}

// 判断如果加载了WordPress
if(defined('ABSPATH')) {

// 前台访问打印
add_action('wp_footer','wp2pcs_video_player_script',99);
function wp2pcs_video_player_script() {
?>
<style>
<?php wp2pcs_video_player_css_in_admin_editor(); ?>
.wp2pcs-video-player a {background-image:url(<?php echo plugins_url('assets/video-play.png',WP2PCS_PLUGIN_NAME); ?>);}
</style>
<script>window.jQuery || document.write('<script type="text/javascript" src="<?php echo plugins_url("assets/jquery-2.1.1.min.js",WP2PCS_PLUGIN_NAME); ?>">\x3C/script>');</script>
<script type="text/javascript">
jQuery(function($){
  $(document).on('click','.wp2pcs-video-player',function(e){
    e.preventDefault();
    var $this = $(this),
        path = $this.attr('data-path'),
        md5 = $this.attr('data-md5');
    $this.html('<iframe src="http://pan.baidu.com/res/static/thirdparty/guanjia/guanjia_play.html?path=' + path + '&md5=' + md5 + '" frameborder="0" framescroll="none"></iframe>');
  });
});
</script>
<?php
}

// 加入到后台编辑器中
add_action( 'init','wp2pcs_video_player_css_in_admin_editor_link');
function wp2pcs_video_player_css_in_admin_editor_link() {
  add_editor_style(plugins_url('hook/video-script.php?script=style.css',WP2PCS_PLUGIN_NAME));
}

} // endif