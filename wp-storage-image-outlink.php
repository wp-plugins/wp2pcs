<?php

// 通过对URI的判断来获得图片远程信息
add_action('init','wp_storage_print_image',-1);
function wp_storage_print_image(){
	if(is_admin())return;
	$home_url = home_url();
	$home_arr = array_filter(explode('/',$home_url));
	$current_uri = urldecode($_SERVER["REQUEST_URI"]);
	$uri_arr = array_values(array_filter(explode('/',$current_uri)));
	$outlink_perfix = trim(get_option('wp_storage_to_pcs_outlink_perfix'));
	$install_in_sub_dir = array_intersect($home_arr,$uri_arr); // 判断是否安装在子目录
	if(!empty($uri_arr)){
		if(empty($install_in_sub_dir)){
			$outlink_uri = $uri_arr[0];
		}else{
			$outlink_uri = $uri_arr[count($install_in_sub_dir)];
		}
		if($outlink_uri == $outlink_perfix){
			$outlink_type = get_option('wp_storage_to_pcs_outlink_type');
			if(strpos($_SERVER['HTTP_REFERER'],home_url()) !== 0){
				header("Content-Type: text/html; charset=utf-8");
				echo '防盗链！ ';
				if($outlink_type == '200')echo '<a href="'.home_url($current_uri).'">原图</a> ';
				echo '<a href="'.home_url().'">首页</a>';
				exit;
			}
			$root_dir = trim(get_option('wp_storage_to_pcs_root_dir'));
			$access_token = trim(get_option('wp_to_pcs_access_token'));
			$image_path = $root_dir.str_replace('/'.$outlink_uri,'',$current_uri);
			if(get_option('wp_to_pcs_app_key') == 'false')$outlink_type = '200';
			if($outlink_type == '200'){
				$pcs = new BaiduPCS($access_token);
				$result = $pcs->thumbnail($image_path,1600,1600,100);
				header('Content-type: image/jpeg');
				echo $result;
			}else{
				$image_outlink = 'https://pcs.baidu.com/rest/2.0/pcs/thumbnail?method=generate&access_token='.$access_token.'&path='.$image_path.'&quality=100&width=1600&height=1600';
				header('Location:'.$image_outlink);
			}
			exit;
		}
	}
}