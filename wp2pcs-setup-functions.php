<?php

// 创建一个函数，用来获取当前PHP的执行时间
function get_unix_timestamp(){   
    list($msec,$sec) = explode(' ',microtime());
    return (float)$sec+(float)$msec;
}
// 利用上面的函数，获取php开始执行的时间戳。注意，这是一个全局函数
$php_begin_run_time = get_unix_timestamp();

// 创建一个函数，获取php执行了的时间，以秒为单位（浮点数）
function get_php_run_time(){
	global $php_begin_run_time;
	$php_run_time = get_unix_timestamp() - $php_begin_run_time;
	return $php_run_time;
}

// 创建一个函数，判断插件是否已经激活
function is_wp_to_pcs_active(){
	$app_key = get_option('wp_to_pcs_app_key');
	$access_token = get_option('wp_to_pcs_access_token');
	if(!$app_key || !$access_token){
		return false;
	}
	return true;
}

// 获取当前访问的URL地址
function wp_to_pcs_wp_current_request_url($query = array(),$remove = array()){
	// http://www.ludou.org/how-to-get-the-current-url-in-wordpress.html
	global $wp;
	$home_root = substr_count(home_url(),'/') <= 3 ? true : false;
	$permalink = trim(get_option('permalink_structure')) != '' ? true : false;
	if($home_root){
		$current_url = home_url(add_query_arg(array()));
	}
	if(!$home_root && $permalink){
		$current_url = home_url(add_query_arg(array(),$wp->request));
	}
	if(!$home_root && !$permalink){
		$current_url = add_query_arg($wp->query_string,'',home_url($wp->request));
	}
	if($current_url == home_url()){
		$current_url = home_url('/');
	}
	$parse_url = parse_url($current_url);
	if(is_array($query) && !empty($query)){
		parse_str($parse_url['query'],$parse_query);
		$parse_query = array_merge($parse_query,$query);
		if(!empty($remove))foreach($remove as $key){
			if(isset($parse_query[$key]))unset($parse_query[$key]);
		}
		$parse_query = http_build_query($parse_query);
		$current_url = str_replace($parse_url['query'],'?'.$parse_query,$current_url);
	}elseif($query === false){
		$current_url = str_replace('?'.$parse_url['query'],'',$current_url);
	}
	return $current_url;
}

// 判断文件或目录是否真的有可写权限
// http://blog.csdn.net/liushuai_andy/article/details/8611433
if ( ! function_exists('is_really_writable'))  
{  
    function is_really_writable($file)  
    {  
        // If we're on a Unix server with safe_mode off we call is_writable  
        if (DIRECTORY_SEPARATOR == '/' AND @ini_get("safe_mode") == FALSE)  
        {  
            return is_writable($file);  
        }  
  
        // For windows servers and safe_mode "on" installations we'll actually  
        // write a file then read it.  Bah...  
        if (is_dir($file))  
        {  
            $file = rtrim($file, '/').'/'.md5(mt_rand(1,100).mt_rand(1,100));  
  
            if (($fp = @fopen($file, FOPEN_WRITE_CREATE)) === FALSE)  
            {  
                return FALSE;  
            }  
  
            fclose($fp);  
            @chmod($file, DIR_WRITE_MODE);  
            @unlink($file);  
            return TRUE;  
        }  
        elseif ( ! is_file($file) OR ($fp = @fopen($file, FOPEN_WRITE_CREATE)) === FALSE)  
        {  
            return FALSE;  
        }  
  
        fclose($fp);  
        return TRUE;  
    }  
}