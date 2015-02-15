<?php

// 判断如果加载了WordPress
if(defined('ABSPATH')) {

// 前台访问打印
add_action('wp_footer','wp2pcs_video_player_script',0);
function wp2pcs_video_player_script() {
$site_id = get_option('wp2pcs_site_id');
if($site_id && get_option('wp2pcs_site_code') && get_option('wp2pcs_video_m3u8')) :
?>
<style>
<?php
echo '.wp2pcs-video-player{display:block;margin:1em auto;cursor:pointer;background:url('.plugins_url('assets/video-play.png',WP2PCS_PLUGIN_NAME).') no-repeat center #f5f5f5;-moz-opacity:0.6;opacity:0.6;overflow:hidden;border:0;}';
echo '.wp2pcs-video-player a{display:block;min-width:480px;min-height:360px;margin: auto;}';
echo '.wp2pcs-video-player:hover{-moz-opacity:1;opacity:1;}';
echo '.wp2pcs-video-player img{-moz-opacity:0.6;opacity:0.6;width:100%;height:100%;}';
echo '.wp2pcs-video-playing{display:block;margin:auto;background:url('.plugins_url('assets/loading.gif',WP2PCS_PLUGIN_NAME).') no-repeat center;}';
?>
</style>
<script>window.jQuery || document.write('<script type="text/javascript" src="<?php echo plugins_url("assets/jquery-2.1.1.min.js",WP2PCS_PLUGIN_NAME); ?>">\x3C/script>');</script>
<script type="text/javascript">
<?php
// 如果是付费用户
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
add_action('init','wp2pcs_admin_editor_videoplay_style');
function wp2pcs_admin_editor_videoplay_style() {
  add_editor_style(plugins_url('hook/video-script.php?script=style.css',WP2PCS_PLUGIN_NAME));
}


}
// 如果没有加载WordPress的话
else {


// 显示播放器
if(isset($_GET['path']) && !empty($_GET['path']) && isset($_GET['md5']) && !empty($_GET['md5'])) {
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8"/>
<title>WP2PCS视频播放</title>
<?php
// 判断来路，如果不是当前网站，不显示任何内容
$host = $_SERVER["HTTP_HOST"];
$host = strpos($host,':') === false ? $host : substr($host,0,strpos($host,':'));
$referer = isset($_SERVER["HTTP_REFERER"]) && !empty($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : null;
if($referer && strpos($_SERVER["HTTP_REFERER"],$host) !== false) {
?>
<base href="http://pan.baidu.com"/>
<link href="/res/static/thirdparty/guanjia/css/guanjia_video_all.css?t=201412054621" rel="stylesheet"/>
</head>
<body>
<div class="guanjia_panl" id="guanjia_panl"></div>
<script src="/res/static/thirdparty/guanjia/js/guanjia_video_all.js?t=201412054621" type="text/javascript"></script>
<script src="/res/static/thirdparty/flashvideo/js/cyberplayer.min.js" type="text/javascript"></script>
<script type="text/javascript">/*<![CDATA[*/$(document).ready(function(){var D=function(){disk.ui.VideoFlash.prototype.getVideoPath=function(){return"/api/streaming?path="+disk.getParam("path")+"&type=M3U8_AUTO_480";};C=decodeURIComponent(C);var A=disk.ui.VideoFlash.obtain(),_={path:C,target:"guanjia_panl",type:2,md5:E,isGuanJia:true,onSeek:function(){},onTime:function(){}};A.play(_);disk.ui.VideoFlash.getStorageItem(E,function(_){if(disk.ui.VideoFlash.flashPlayer){disk.ui.VideoFlash.flashPlayer.seek(_);}});if(disk.DEBUG){}},C=disk.getParam("path"),E=disk.getParam("md5")||"",B=$("#guanjia_panl"),_=function(){B.html('<div class="nofile">\u6587\u4ef6\u52a0\u8f7d\u5931\u8d25</div>');};if(!C){_();return;}if(parseInt(disk.getParam("safebox"),10)===1){D();$.get("/api/streaming?path="+disk.getParam("path")+"&type=M3U8_AUTO_480",function(_){try{_=$.parseJSON(_);if(_.errno===27){try{BDHScript.throwEvent("LockSafebox",{lock:1});}catch(A){}}else{}}catch(A){}});}else{C=decodeURIComponent(C);var F=disk.ui.VideoFlash.obtain(),A={path:C,target:"guanjia_panl",type:2,md5:E,isGuanJia:true,onSeek:function(){},onTime:function(){}};F.play(A);disk.ui.VideoFlash.getStorageItem(E,function(_){if(disk.ui.VideoFlash.flashPlayer){disk.ui.VideoFlash.flashPlayer.seek(_);}});if(disk.DEBUG){}}});/*]]>*/</script>
<script>jQuery(function($){$(document).bind("contextmenu",function(e){return false;});});</script>
<?php }else{ ?>
</head>
<body>
<?php } ?>
</body>
</html>
<?php
  exit();
}

// 直接访问文件的时候打印CSS
if(isset($_GET['script']) && $_GET['script'] == 'style.css') {
  header('Content-Type: text/css; charset=utf-8');
  echo '.wp2pcs-video-player{display:block;width:480px;height:360px;margin: 1em auto;background:url(../assets/video-play.png) no-repeat center #f5f5f5;-moz-opacity:0.6;opacity:0.6;}';
  echo '.wp2pcs-video-player:hover{-moz-opacity:1;opacity:1;}';
  exit;
}

} // 没有加载Wordpress结束
