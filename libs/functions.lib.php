<?php

/*
 * 利用原生的PHP和WP函数写的相关函数
 */

// 读取http缓存
function wp2pcs_cache() {
  header("Cache-Control: private, max-age=10800, pre-check=10800");
  header("Pragma: private");
  header("Expires: " . date(DATE_RFC822,strtotime(" 2 day")));
  if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
    header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE'],true,304);
    exit;
  }
}

if(!function_exists('get_by_curl')) :
function get_by_curl($url,$post = false,$referer = false){
  $ch = curl_init();
  curl_setopt($ch,CURLOPT_URL,$url);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  if($referer) {
    curl_setopt ($ch,CURLOPT_REFERER,$referer);
  }
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FAILONERROR, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  if($post){
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
  }
  $result = curl_exec($ch);
  curl_close($ch);
  return $result;
}
endif;
