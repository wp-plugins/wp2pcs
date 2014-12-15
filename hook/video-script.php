<?php

// 基本的视频播放器样式css，因为两种情况下都要用，所以放在最前面
function wp2pcs_video_player_css_in_admin_editor() {
?>
.wp2pcs-video-player {display:block;width:480px;height:360px;margin: 1em auto;}
.wp2pcs-video-player a {display:block;width:100%;height:100%;border:#dedede dashed 5px;background-repeat:no-repeat;background-position:center;text-decoration:none;
  -moz-opacity: 0.6;
  opacity:      0.6;
}
.wp2pcs-video-player a:hover {
  -moz-opacity: 1;
  opacity:      1;
}
.wp2pcs-video-player iframe {display:block;width:100%;height:100%;}
<?php
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
function get_extension_by_file_name(pathfilename) {
  var reg = /(\\+)/g;  
  var pfn = pathfilename.replace(reg, "#");  
  var arrpfn = pfn.split("#");  
  var fn = arrpfn[arrpfn.length - 1];  
  var arrfn = fn.split(".");  
  return arrfn[arrfn.length - 1];  
}
jQuery(function($){
  $(document).on('click','.wp2pcs-video-player a',function(e){
    e.preventDefault();
    var $this = $(this).parent(),
        path = $this.attr('data-path'),
        md5 = $this.attr('data-md5'),
        ext = get_extension_by_file_name(path);
    if(md5 == undefined || md5 == '') return true;
    $this.css('background-color','#f5f5f5').html('<iframe src="<?php echo plugins_url("hook/video-script.php",WP2PCS_PLUGIN_NAME); ?>?path=' + path + '&md5=' + md5 + '&video=.' + ext + '" frameborder="0" framescroll="none"></iframe>');
    return false;
  });
});
</script>
<?php
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
if(isset($_GET['path']) && !empty($_GET['path']) && isset($_GET['md5']) && !empty($_GET['md5']) && isset($_GET['video']) && !empty($_GET['video'])) {
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8"/>
<title>WP2PCS视频播放</title>
<base href="http://pan.baidu.com"/>
<link href="/res/static/thirdparty/guanjia/css/guanjia_video_all.css?t=201412054621" rel="stylesheet"/>
</head>
<body>
<?php
// 判断来路，如果不是当前网站，不显示任何内容
$host = $_SERVER["HTTP_HOST"];
$host = strpos($host,':') === false ? $host : substr($host,0,strpos($host,':'));
$referer = isset($_SERVER["HTTP_REFERER"]) && !empty($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : null;
if($referer && strpos($_SERVER["HTTP_REFERER"],$host) !== false) {
?>
<div class="guanjia_panl" id="guanjia_panl">
</div>
<script src="/res/static/thirdparty/guanjia/js/guanjia_video_all.js?t=201412054621" type="text/javascript"></script>
<script src="/res/static/thirdparty/flashvideo/js/cyberplayer.min.js" type="text/javascript"></script>
<script type="text/javascript">/*<![CDATA[*/$(document).ready(function(){var D=function(){disk.ui.VideoFlash.prototype.getVideoPath=function(){return"/api/streaming?path="+disk.getParam("path")+"&type=M3U8_AUTO_480";};C=decodeURIComponent(C);var A=disk.ui.VideoFlash.obtain(),_={path:C,target:"guanjia_panl",type:2,md5:E,isGuanJia:true,onSeek:function(){},onTime:function(){}};A.play(_);disk.ui.VideoFlash.getStorageItem(E,function(_){if(disk.ui.VideoFlash.flashPlayer){disk.ui.VideoFlash.flashPlayer.seek(_);}});disk.ui.GuanJiaVideo.installFuncTips();if(disk.DEBUG){}},C=disk.getParam("path"),E=disk.getParam("md5")||"",B=$("#guanjia_panl"),_=function(){B.html('<div class="nofile">\u6587\u4ef6\u52a0\u8f7d\u5931\u8d25</div>');};if(!C){_();return;}if(parseInt(disk.getParam("safebox"),10)===1){D();$.get("/api/streaming?path="+disk.getParam("path")+"&type=M3U8_AUTO_480",function(_){try{_=$.parseJSON(_);if(_.errno===27){try{BDHScript.throwEvent("LockSafebox",{lock:1});}catch(A){}}else{}}catch(A){}});}else{C=decodeURIComponent(C);var F=disk.ui.VideoFlash.obtain(),A={path:C,target:"guanjia_panl",type:2,md5:E,isGuanJia:true,onSeek:function(){},onTime:function(){}};F.play(A);disk.ui.VideoFlash.getStorageItem(E,function(_){if(disk.ui.VideoFlash.flashPlayer){disk.ui.VideoFlash.flashPlayer.seek(_);}});disk.ui.GuanJiaVideo.installFuncTips();if(disk.DEBUG){}}});/*]]>*/</script>
<script>
jQuery(function($){
  $('.video-functions-tips').remove();
  $(document).bind("contextmenu",function(e){   
    return false;   
  });
});
</script>
<?php
}
?>
</body>
</html>
<?php
  exit();
}

// 直接访问文件的时候打印CSS
if(isset($_GET['script']) && $_GET['script'] == 'style.css') {
  header("Cache-Control: private, max-age=10800, pre-check=10800");
  header("Pragma: private");
  header("Expires: " . date(DATE_RFC822,strtotime(" 2 day")));
  if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
    header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE'],true,304);
    exit;
  }
  header('Content-Type: text/css; charset=utf-8');
  wp2pcs_video_player_css_in_admin_editor();
  echo '.wp2pcs-video-player a {background-image:url(../assets/video-play.png);}';
  exit;
}

}