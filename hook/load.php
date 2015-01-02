<?php

// 先获取文件的相对路径
$path = null;
if(get_option('permalink_structure')) {
  $URI = str_replace('+','{plus}',$_SERVER['REQUEST_URI']);
  $URI = urldecode($URI);
  $URI = str_replace('{plus}','+',$URI);
  $pos = strpos($URI,'?');
  if($pos !== false) {
    $URI = substr($URI,0,$pos);
  }
  $pos = strpos($URI,'/wp2pcs/');
  if($pos !== false) {
    $path = substr($URI,$pos+7);
  }
}
$GET = str_replace('+','{plus}',$_GET['wp2pcs']);
$GET = urldecode($GET);
$GET = str_replace('{plus}','+',$GET);
$path = !$path && $GET ? $GET : $path;

if(!$path) : return;
elseif($path == '/') : return;
elseif(strpos($path,'.') === false) : return;
else :

$path = BAIDUPCS_REMOTE_ROOT.'/load'.$path;
$file_ext = strtolower(substr($path,strrpos($path,'.')+1));
$file_name = substr($path,strrpos($path,'/')+1);

// 读取http缓存
if(!in_array($file_ext,array('asf','avi','flv','mkv','mov','mp4','wmv','3gp','3g2','mpeg','rm','rmvb','qt'))) {
  wp2pcs_http_cache();
}

global $BaiduPCS;

// 先检查文件是否存在
$meta = $BaiduPCS->getMeta($path);
$meta = json_decode($meta);
// 如果文件不存在，就试图从共享目录中抓取文件
if(isset($meta->error_code) && $meta->error_code == '31066') {
  $path = str_replace(BAIDUPCS_REMOTE_ROOT.'/load/','/apps/wp2pcs/share/',$path);
  $meta = $BaiduPCS->getMeta($path);
  $meta = json_decode($meta);
}
// 如果抓取错误
if(isset($meta->error_msg)){
  header("Content-Type: text/html; charset=utf8");
  echo $meta->error_msg;
  exit;
}

$wp2pcs_cache_count = (int)get_option('WP2PCS_CACHE_'.$path);
$wp2pcs_load_cache = (int)get_option('wp2pcs_load_cache');
$result = null;
// 获取缓存
if($wp2pcs_cache_count >= WP2PCS_CACHE_COUNT && $wp2pcs_load_cache) {
  $result = wp2pcs_get_cache($path);
}

if(in_array($file_ext,array('jpg','jpeg','png','gif','bmp'))) {
  if(!$result) {
    $result = $BaiduPCS->downloadStream($path);
  }
  header('Content-type: image/jpeg');
}
elseif(in_array($file_ext,array('mp3','ogg','wma','wav','mp3pro','mid','midi'))) {
  if(!$result) {
    $result = $BaiduPCS->downloadStream($path);
  }
  if($file_ext == 'mp3' || $file_ext == 'mp3pro') header("Content-Type: audio/mpeg");
  elseif($file_ext == 'ogg') header('Content-Type: application/ogg');
  elseif($file_ext == 'wma') header('Content-Type: audio/x-ms-wma');
  elseif($file_ext == 'wav') header('Content-Type: audio/x-wav');
  elseif($file_ext == 'mid' || $file_ext == 'midi') header('Content-Type: audio/midi');
  else header('Content-Type: application/octet-stream');
  header('Content-Length: '.strlen($result));
  header('Content-Disposition: inline; filename="'.$file_name.'"');
  header('Accept-Ranges: bytes');
  header('X-Pad: avoid browser bug');
}
elseif(in_array($file_ext,array('asf','avi','flv','mkv','mov','mp4','wmv','3gp','3g2','mpeg','rm','rmvb','qt'))) {
  $length = @$meta->list[0]->size;
  if(!@$length) {
    header("Content-Type: text/html; charset=utf8");
    echo('没有获取到视频，请稍后试试。');
    exit;
  }
  
  if($file_ext == 'asf') header('Content-Type: video/x-ms-asf');
  elseif($file_ext == 'avi') header('Content-Type: video/x-msvideo');
  elseif($file_ext == 'flv') header('Content-Type: video/x-flv');
  elseif($file_ext == 'mvk') header('Content-Type: video/x-matroska');
  elseif($file_ext == 'mov' || $file_ext == 'qt') header('Content-Type: video/quicktime');
  elseif($file_ext == 'mp4') header('Content-Type: video/mp4');
  elseif($file_ext == 'mvk' || $file_ext == 'wmv') header('Content-Type: video/x-ms-wmv');
  elseif($file_ext == 'mpeg' || $file_ext == 'mpg' || $file_ext == 'mpe') header('Content-Type: video/mpeg');
  elseif($file_ext == 'rm') header('Content-Type: application/vnd.rn-realmedia');
  elseif($file_ext == 'rmvb') header('Content-Type: application/vnd.rn-realmedia-vbr');
  else header('Content-Type: application/octet-stream');
  
  header("Accept-Ranges: 0-$end");
  header('X-Pad: avoid browser bug');
  
  $size = $length;
  $start = 0;
  $end = $size - 1; // 这个1要好好理解，长度和指针索引之间的关系，末尾指针索引=长度-1
  if(isset($_SERVER['HTTP_RANGE'])) {
    $c_start = $start;
    $c_end = $end;
    list(,$range) = explode('=',$_SERVER['HTTP_RANGE'],2);
    // 不含range
    if(strpos($range,',') !== false) {
      header('HTTP/1.1 416 Requested Range Not Satisfiable');
      header("Content-Range: bytes $start-$end/$size");
      exit;
    }
    // 如果range:-xx
    if($range == '-') {
      $c_start = $size - substr($range,1);
    }
    // 如果range:xx-oo
    else {
      $range = explode('-',$range);
      $c_start = $range[0];
      $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $c_end;
    }
    // 如果range中的end比文件本身的size都大的话，采用size的大小，也就是$end的值
    $c_end = ($c_end > $end) ? $end : $c_end;
    // 如果range中的start比end还大，或者start或end比文件本身的size都大的话
    if($c_start > $c_end || $c_start > $size -1 || $c_end >= $size) {
      header('HTTP/1.1 416 Requested Range Not Satisfiable');
      header("Content-Range: bytes $start-$end/$size");
      exit;
    }
    // 如果上面这些情况都不存在，或者已经处理好了，那么就得到了最终我们想要的start和end，以及通过start和end获取length
    $start = $c_start;
    $end = $c_end;
    $length = $end - $start + 1;
    header('HTTP/1.1 206 Partial Content');
  }

  // 如果不存在range，length就是文件的大小，如果存在，则使用的是上面经过处理的start,end,length
  header("Content-Length: ".$length);
  header("Content-Range: bytes $start-$end/$size");

  // 下面这段代码更凶残，它把上面得到的start-end之间的range再次按buffer进行分割，以保证流的更小
  $buffer = min((int)($length/16),1024*16);
  $current = $start;
  // 当当前指针没有到末尾（指http range的末尾）时才执行
  while($current < $end) {
    // 获取当前指针到buffer长度处的流
    $from = $current;
    $to = $current + $buffer - 1;
    // 如果当前指针加上buffer大于末尾指针，那么直接让buffer等于剩余的长度
    if($current + $buffer > $end) {
      $to = $end;
	    $buffer = $size - $current;
    }
    $output = $BaiduPCS->downloadStream($path,array('Range' => "bytes=$from-$to"));
    // 把当前指针往后移动buffer的长度
    $current += $buffer;
    // 输出当前指针往后buffer长度的流
    ob_clean();
    echo $output;
    flush();
  }
  exit();
}
else{
  if(!$result) {
    $result = $BaiduPCS->download($path);
  }
  header("Content-Type: application/octet-stream");
  header('Content-Disposition:inline;filename="'.$file_name.'"');
  header('Accept-Ranges: bytes');
}

ob_clean();
echo $result;
flush();

// 缓存起来
if($wp2pcs_load_cache && !is_admin()) {
  if($wp2pcs_cache_count < WP2PCS_CACHE_COUNT) {
    update_option('WP2PCS_CACHE_'.$path,$wp2pcs_cache_count ++);
  }
  elseif(!wp2pcs_has_cache($path)) {
    wp2pcs_set_cache($path,$result);
  }
  
}

exit;
endif;// end of path usefullness
