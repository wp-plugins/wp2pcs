<?php

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
				if($outlink_type == '200')echo '<a href="'.home_url($current_uri).'">下载</a> ';
				echo '<a href="'.home_url().'">首页</a>';
				exit;
			}
			$file_path = $root_dir.str_replace('/'.$outlink_uri,'',$current_uri);
			$access_token = trim(get_option('wp_to_pcs_access_token'));
			if(get_option('wp_to_pcs_app_key') == 'false')$outlink_type = '200';
			if($outlink_type == '200'){
				$root_dir = trim(get_option('wp_storage_to_pcs_root_dir'));
				$pcs = new BaiduPCS($access_token);
				$file_name = basename($file_path);
				header('Content-Disposition:attachment;filename="'.$file_name.'"');
				header('Content-Type:application/octet-stream');
				$result = $pcs->download($file_path);
				echo $result;
			}else{
				$download_link = 'https://pcs.baidu.com/rest/2.0/pcs/stream?method=download&access_token='.$access_token.'&path='.$file_path;
				header('Location:'.$download_link);
			}
			exit;
		}
	}
}