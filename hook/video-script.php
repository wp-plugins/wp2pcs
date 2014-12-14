<?php

if(!function_exists('get_by_curl')) :
function get_by_curl($url,$post = false,$referer = false){
  $ch = curl_init();
  curl_setopt($ch,CURLOPT_URL,$url);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  if($referer) {
    curl_setopt ($ch,CURLOPT_REFERER,$referer);
  }
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  if($post){
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
  }
  $result = curl_exec($ch);
  curl_close($ch);
  return $result;
}
endif;

// 显示播放器
if(isset($_GET['path']) && !empty($_GET['path']) && isset($_GET['md5']) && !empty($_GET['md5']) && isset($_GET['video']) && !empty($_GET['video'])) {
  header("Cache-Control: private, max-age=10800, pre-check=10800");
  header("Pragma: private");
  header("Expires: " . date(DATE_RFC822,strtotime(" 2 day")));
  if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
    header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE'],true,304);
    exit;
  }

  $path = $_GET['path'];
  $md5 = $_GET['md5'];
  $url = 'http://pan.baidu.com/res/static/thirdparty/guanjia/guanjia_play.html?path='.$path.'&md5='.$md5;
  $html = get_by_curl($url,false,$url);
  $html = str_replace('</title>','</title><base href="http://pan.baidu.com/" />',$html);
  $html = str_replace('</body>','',$html);
  $html = str_replace('</html>','',$html);
  echo $html;
?>
<script>
jQuery(function($){
  $('.video-functions-tips').remove();
});
</script>
</body>
</html>
<?php
  exit();
}

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

// 直接访问文件的时候打印
if(isset($_GET['script']) && $_GET['script'] == 'style.css') {
  header('Content-Type: text/css; charset=utf-8');
  wp2pcs_video_player_css_in_admin_editor();
  echo '.wp2pcs-video-player a {background-image:url(../assets/video-play.png);}';
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

// 加入到后台编辑器中
add_action( 'init','wp2pcs_video_player_css_in_admin_editor_link');
function wp2pcs_video_player_css_in_admin_editor_link() {
  add_editor_style(plugins_url('hook/video-script.php?script=style.css',WP2PCS_PLUGIN_NAME));
}

} // endif
