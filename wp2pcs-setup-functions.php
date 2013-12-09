<?php

// 替换字符串中第一次出现的子串
function str_replace_first($find,$replace,$string){
	$position = strpos($string,$find);
	if($position !== false){
		$length = strlen($find); 
		$string = substr_replace($string,$replace,$position,$length);
		return $string;
	}else{
		return false;
	}
}

// 替换字符串中最后一次出现的子串
function str_replace_last($find,$replace,$string){
	$position = strrpos($string,$find);
	if($position !== false){
		$length = strlen($find); 
		$string = substr_replace($string,$replace,$position,$length);
		return $string;
	}else{
		return false;
	}
}

// 创建一个函数，判断wordpress是否安装在子目录中
function get_blog_install_in_subdir(){
	// 获取home_url其中的path部分，以此来判断是否安装在子目录中
	$install_in_sub_dir = parse_url(home_url(),PHP_URL_PATH);
	if($install_in_sub_dir){
		return $install_in_sub_dir;
	}else{
		return false;
	}
}

// 判断wordpress是否安装在win主机，并开启了重写
function get_blog_install_software(){
	$permalink_structure = get_option('permalink_structure');
	$software = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '';
	if(strpos($software,'IIS') !== false){
		$software = 'IIS';
	}elseif(strpos($software,'Apache') !== false){
		$software = 'Apache';
	}elseif(strpos($software,'NginX') !== false){
		$software = 'NginX';
	}else{
		$software = 'Others';
	}
	$install_root = ABSPATH;
	$install_in_subdir = get_blog_install_in_subdir();
	if($software == 'IIS' && $install_in_subdir){
		$install_root = str_replace_last($install_in_subdir.'/','',$install_root);
	}
	$is_rewrited = false;
	if($permalink_structure){
		if(file_exists($install_root.'httpd.ini') || file_exists($install_root.'.htaccess') || file_exists($install_root.'httpd.conf') || file_exists($install_root.'app.conf') || file_exists($install_root.'config.yaml'))
			$is_rewrited = true;
	}
	// 如果固定链接没有填写，也不存在httpd.ini，那么就直接返回，认为不是在IIS上
	if(!$is_rewrited){
		return false;
	}
	// 固定链接正确填写
	else{
		return $software;
	}
}

// 通过一个函数用来计算当前附件的访问的真正有效的uri
function get_outlink_real_uri($uri,$perfix){
	// $uri是home_url后面的URL字串，例如当前的uri是http://yourdomain.com/yourblog/?image/test.jpg，那么$uri==/?image/test.jpg
	// 但在一些特殊情况下，如视频前缀为index.php/video，以及IIS上，$uri就有可能为index.php/?image/test.jpg
	// $perfix是指要处理的附件的前缀，例如可以是get_option('wp_storage_to_pcs_image_perfix');(图片前缀)
	// 你要通过这个函数返回真正有效的uri，如上所述$uri==index.php/?image/test.jpg，你应该尽可能的让函数返回/?image/test.jpg
	if(get_blog_install_software() == 'IIS'){
		if(
			(strpos($uri,'/index.php/')===0 
			&& strpos($perfix,'index.php/')!==0
			&& strpos($uri,'/index.php/'.$perfix)===0)
			||
			(strpos($uri,'/index.php/index.php/')===0 
			&& strpos($image_perfix,'index.php/')===0
			&& strpos($uri,'/index.php/'.$perfix)===0)
		){
			$uri = str_replace_first('/index.php','',$uri);
		}
	}
	return $uri;
}

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
	// 获取当前URL
	$current_url = 'http';
	if ($_SERVER["HTTPS"] == "on"){
		$current_url .= "s";
	}
	$current_url .= "://";
	if($_SERVER["SERVER_PORT"] != "80"){
		$current_url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	}else{
		$current_url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	// 是否要进行参数处理
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
function is_really_writable($file)  {  
	// 是否开启安全模式
	if(DIRECTORY_SEPARATOR == '/' AND @ini_get("safe_mode") == FALSE){
		return is_writable($file);
	}
	// 如果是目录的话
	if(is_dir($file)){
		$file = rtrim($file, '/').'/'.md5(mt_rand(1,100).mt_rand(1,100));
		if(($fp = @fopen($file,'w+')) === FALSE){
			return FALSE;  
		}
		fclose($fp);
		@chmod($file,'0755');
		@unlink($file);
		return TRUE;
	}
	// 如果是不是文件，或文件打不开的话
	elseif(!is_file($file) OR ($fp = @fopen($file,'w+')) === FALSE){
		return FALSE;
	}
	fclose($fp);
	return TRUE;
}


// 设置全局参数
function set_php_ini($name){
	if($name == 'session'){
		if(!defined('WP_TEMP_DIR'))define('WP_TEMP_DIR',sys_get_temp_dir());
		if(is_really_writable(WP_TEMP_DIR)){
			ini_set('session.save_path',WP_TEMP_DIR);// 重新规定session的存储位置
			session_start();
		}
	}elseif($name == 'limit'){
		set_time_limit(0); // 延长执行时间，防止备份失败
		ini_set('memory_limit','200M'); // 扩大内存限制，防止备份溢出		// 考虑到流量问题，必须增加缓存能力
	}elseif($name == 'timezone'){
		date_default_timezone_set("PRC");// 使用东八区时间，如果你是其他地区的时间，自己修改
	}
}