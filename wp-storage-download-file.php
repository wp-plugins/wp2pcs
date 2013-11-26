<?php

// 创建一个函数，用来在wordpress中打印下载地址
function wp2pcs_download_link($file_path){
	// file_path是指相对于后台保存的存储目录的路径
	// 例如 $file_path = /test/test.jpg ，就是使用你的网盘目录 /apps/wp2pcs/...../test/test.jpg
	// 其中.....是指你填写的用于保存文件的网盘目录，/test/是你在这个目录下随意创建的一个目录，test.jpg就是要打印的图片
	// 注意最前面加/
	$download_perfix = trim(get_option('wp_storage_to_pcs_download_perfix'));
	$download_link = '/'.$down_perfix.$file_path;
	return home_url($download_link);
}

// 通过对URI的判断来确定是否是下载文件的链接
add_action('init','wp_storage_download_file',-1);
function wp_storage_download_file(){
	if(is_admin())return;
	$home_url = home_url();
	$home_arr = array_filter(explode('/',$home_url));
	$current_uri = $_SERVER["REQUEST_URI"];// 和图片外链不同，如果这个地方urldecode，就会造成下载错误
	$uri_arr = array_values(array_filter(explode('/',$current_uri)));
	$download_perfix = trim(get_option('wp_storage_to_pcs_download_perfix'));
	$install_in_sub_dir = array_intersect($home_arr,$uri_arr);
	if(!empty($uri_arr)){
		if(empty($install_in_sub_dir)){
			$outlink_uri = $uri_arr[0];
		}else{
			$outlink_uri = $uri_arr[count($install_in_sub_dir)];
		}
		if($outlink_uri == $download_perfix){
			$outlink_type = get_option('wp_storage_to_pcs_outlink_type');
			if(strpos($_SERVER['HTTP_REFERER'],home_url()) !== 0){
				header("Content-Type: text/html; charset=utf-8");
				echo '防盗链！ ';
				echo '<a href="'.home_url($current_uri).'">下载</a> ';
				echo '<a href="'.home_url().'">首页</a>';
				exit;
			}
			$root_dir = get_option('wp_storage_to_pcs_root_dir');
			$access_token = WP2PCS_APP_TOKEN;
			$file_path = $root_dir.str_replace_first_time('/'.$outlink_uri.'/','/',$current_uri);
			$file_path = str_replace('//','/',$file_path);
			//if(get_option('wp_to_pcs_app_key') === 'false')$outlink_type = '200';
			if($outlink_type == '200'){
				$root_dir = trim(get_option('wp_storage_to_pcs_root_dir'));
				$pcs = new BaiduPCS($access_token);
				$file_name = basename($file_path);
				header('Content-Disposition:attachment;filename="'.$file_name.'"');
				header('Content-Type:application/octet-stream');
				$result = $pcs->download($file_path);
				echo $result;
			}else{
				$site_id = get_option('wp_to_pcs_site_id');
				$access_token = substr($access_token,0,10);
				//$download_link = 'https://pcs.baidu.com/rest/2.0/pcs/stream?method=download&access_token='.$access_token.'&path='.$file_path;
				$download_link = 'http://wp2pcs.duapp.com/dl?'.$site_id.'+'.$access_token.'+path='.$file_path;
				header('Location:'.$download_link);
			}
			exit;
		}
	}
}