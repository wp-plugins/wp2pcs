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
$path = !$path && isset($_GET['wp2pcs']) && !empty($_GET['wp2pcs']) ? urldecode($_GET['wp2pcs']) : $path;

if(!$path) : return;
elseif($path == '/') : return;
elseif(strpos($path,'.') === false) : return;
else :

// 获取完整的路径
$path = BAIDUPCS_REMOTE_ROOT.'/load'.$path;

$file_ext = strtolower(substr($path,strrpos($path,'.')+1));
$file_name = substr($path,strrpos($path,'/')+1);

// 读取http缓存
if(!in_array($file_ext,array('asf','avi','flv','mkv','mov','mp4','wmv','3gp','3g2','mpeg','rm','rmvb','qt'))) {
  wp2pcs_cache();
}

global $BaiduPCS;

if(in_array($file_ext,array('jpg','jpeg','png','gif','bmp')) && !isset($_GET['download'])){
  set_time_limit(0);
  $result = $BaiduPCS->downloadStream($path);
  $meta = json_decode($result,true);
  if(isset($meta['error_msg'])){
    header("Content-Type: text/html; charset=utf8");
    echo $meta['error_msg'];
    exit;
  }

  header('Content-type: image/jpeg');
}
elseif(in_array($file_ext,array('mp3','ogg','wma','wav','mp3pro','mid','midi')) && !isset($_GET['download'])) {
  set_time_limit(0);
  $result = $BaiduPCS->downloadStream($path);
  $meta = json_decode($result,true);
  if(isset($meta['error_msg'])){
    header("Content-Type: text/html; charset=utf8");
    echo $meta['error_msg'];
    exit;
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
elseif(in_array($file_ext,array('asf','avi','flv','mkv','mov','mp4','wmv','3gp','3g2','mpeg','rm','rmvb','qt')) && !isset($_GET['download'])) {
  set_time_limit(0);
  $result = $BaiduPCS->downloadStream($path);
  $meta = json_decode($result,true);
  if(isset($meta['error_msg'])){
    header("Content-Type: text/html; charset=utf8");
    echo $meta['error_msg'];
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
  $length = strlen($result);
  $size = $length;
  $start = 0;
  $end = $size - 1;
  if(isset($_SERVER['HTTP_RANGE'])) {
    $c_start = $start;
    $c_end = $end;
    list(,$range) = explode('=',$_SERVER['HTTP_RANGE'],2);
    if(strpos($range,',') !== false) {
      header('HTTP/1.1 416 Requested Range Not Satisfiable');
      header("Content-Range: bytes $start-$end/$size");
      exit;
    }
    if($range == '-') {
      $c_start = $size - substr($range,1);
    }
    else {
      $range = explode('-',$range);
      $c_start = $range[0];
      $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $c_end;
    }
    $c_end = ($c_end > $end) ? $end : $c_end;
    if($c_start > $c_end || $c_start > $size -1 || $c_end >= $size) {
      header('HTTP/1.1 416 Requested Range Not Satisfiable');
      header("Content-Range: bytes $start-$end/$size");
      exit;
    }
    $start = $c_start;
    $end = $c_end;
    $length = $end - $start + 1;
    header('HTTP/1.1 206 Partial Content');
  }
  header("Content-Length: ".$length);
  header("Content-Range: bytes $start-$end/$size");
  
  $buffer = 1024 * 64;
  $current = $start;
  while($current < $end) {
    if($current + $buffer > $end) {
      $buffer = $end - $current + 1;
    }
    $output = substr($result,$start,$buffer);
    $current += $buffer;
    ob_clean();
    echo $output;
    flush();
  }
  exit();

}
else{
  set_time_limit(0);
  $result = $BaiduPCS->download($path);
  $meta = json_decode($result,true);
  if(isset($meta['error_msg'])){
    header("Content-Type: text/html; charset=utf8");
    echo $meta['error_msg'];
    exit;
  }

  header("Content-Type: application/octet-stream");
  header('Content-Disposition:inline;filename="'.$file_name.'"');
  header('Accept-Ranges: bytes');
}

ob_clean();
echo $result;
flush();
exit;
endif;// end of path usefullness