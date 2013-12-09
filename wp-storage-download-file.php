<?php

// 创建一个函数，用来在wordpress中打印下载地址
function wp2pcs_download_link($file_path = false){
	// file_path是指相对于后台保存的存储目录的路径
	// 例如 $file_path = /test/test.jpg ，就是使用你的网盘目录 /apps/wp2pcs/...../test/test.jpg
	// 其中.....是指你填写的用于保存文件的网盘目录，/test/是你在这个目录下随意创建的一个目录，test.jpg就是要打印的图片
	// 注意最前面加/
	$download_perfix = trim(get_option('wp_storage_to_pcs_download_perfix'));
	$download_link = "/$down_perfix/".$file_path;
	$download_link = str_replace('//','/',$download_link);
	return home_url($download_link);
}

// 通过对URI的判断来确定是否是下载文件的链接
add_action('init','wp_storage_download_file',-1);
function wp_storage_download_file(){
	// 只用于前台下载文件
	if(is_admin()){
		return;
	}

	$download_perfix = trim(get_option('wp_storage_to_pcs_download_perfix'));
	$current_uri = urldecode($_SERVER["REQUEST_URI"]);
	$file_uri = $current_uri;
	$file_path = '';

	// 如果不存在前缀，就不执行了
	if(!$download_perfix){
		return;
	}

	// 当采用index.php/download时，大部分主机会跳转，丢失index.php，因此这里要做处理
	if(strpos($download_perfix,'index.php/')===0 && strpos($download_uri,'index.php/')===false){
		$download_perfix = str_replace('index.php/','',$download_perfix);
	}

	// 如果URI中根本不包含$download_perfix，那么就不用再往下执行了
	if(strpos($file_uri,$download_perfix) === false){
		return;
	}

	// 获取安装在子目录
	$install_in_subdir = get_blog_install_in_subdir();
	if($install_in_subdir){
		$file_uri = str_replace_first($install_in_subdir,'',$file_uri);
	}

	// 返回真正有效的URI
	$file_uri = get_outlink_real_uri($file_uri,$file_perfix);

	// 如果URI中根本不包含$download_perfix，那么就不用再往下执行了
	if(strpos($file_uri,'/'.$download_perfix) !== 0){
		return;
	}
	
	// 将前缀也去除，获取文件直接路径
	$file_path = str_replace_first('/'.$download_perfix,'',$file_uri);

	// 如果不存在file_path，也不执行了
	if(!$file_path){
		return;
	}

	// 获取文件真实路径
	$root_dir = get_option('wp_storage_to_pcs_root_dir');
	$file_path = trailingslashit($root_dir).$file_path;
	$file_path = str_replace('//','/',$file_path);
	$outlink_type = get_option('wp_storage_to_pcs_outlink_type');

	// 防盗链
	if(strpos($_SERVER['HTTP_REFERER'],home_url()) !== 0 && get_option('wp_storage_to_pcs_outlink_protact')){
		header("Content-Type: text/html; charset=utf-8");
		echo '防盗链！ ';
		echo '<a href="'.$current_uri.'">下载</a> ';
		echo '<a href="'.home_url('/').'">首页</a>';
		exit;
	}

	if($outlink_type == '200'){
		set_php_ini('limite');
		set_php_ini('timezone');
		set_php_ini('session');
		header("Cache-Control: private, max-age=10800, pre-check=10800");
		header("Pragma: private");
		header("Expires: " . date(DATE_RFC822,strtotime(" 2 day")));
		if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
			header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE'],true,304);
			session_destroy();
			exit;
		}
		// 打印图片到浏览器
		$pcs = new BaiduPCS(WP2PCS_APP_TOKEN);
		$result = $pcs->download($file_path);

		$meta = json_decode($result,true);
		if(isset($meta['error_msg'])){
			echo $meta['error_msg'];
			session_destroy();
			exit;
		}
		
		$file_name = basename($file_path);
		header('Content-Disposition:attachment;filename="'.$file_name.'"');
		header('Content-Type:application/octet-stream');
		ob_clean();
		echo $result;
		session_destroy();
		exit;
	}else{
		$site_id = get_option('wp_to_pcs_site_id');
		$access_token = substr(WP2PCS_APP_TOKEN,0,10);
		$download_link = 'http://wp2pcs.duapp.com/dl?'.$site_id.'+'.$access_token.'+path='.$file_path;
		header('Location:'.$download_link);
	}
	exit;
}