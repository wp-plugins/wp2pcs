<?php

// 创建一个函数，用来在wordpress中打印图片地址
function wp2pcs_image_src($image_path){
	// image_path是指相对于后台保存的存储目录的路径
	// 例如 $file_path = /test/test.jpg
	// 注意最前面加/
	$outlink_perfix = trim(get_option('wp_storage_to_pcs_outlink_perfix'));
	$image_src = '/'.$outlink_perfix.$image_path;
	return home_url($image_src);
}

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
			if(isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],$home_url) !== 0){
				header("Content-Type: text/html; charset=utf-8");
				echo '防盗链！ ';
				if($outlink_type == '200')echo '<a href="'.home_url($current_uri).'">原图</a> ';
				echo '<a href="'.home_url().'">首页</a>';
				exit;
			}
			$root_dir = get_option('wp_storage_to_pcs_root_dir');
			$access_token = WP2PCS_APP_TOKEN;
			$image_path = $root_dir.str_replace_first_time('/'.$outlink_uri.'/','/',$current_uri);
			$image_path = str_replace('//','/',$image_path);
			if($outlink_type == '200'){
				// 考虑到流量问题，必须增加缓存能力
				date_default_timezone_set("PRC");// 把时间控制在中国
				session_start(); 
				header("Cache-Control: private, max-age=10800, pre-check=10800");
				header("Pragma: private");
				header("Expires: " . date(DATE_RFC822,strtotime(" 2 day")));
				if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
				  header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE'],true,304);
				  exit;
				}
				// 缓存结束
				$pcs = new BaiduPCS($access_token);
				//$result = $pcs->thumbnail($image_path,1600,3200,100);
				$result = $pcs->downloadStream($image_path);
				header('Content-type: image/jpeg');
				echo $result;
			}else{
				//$image_outlink = 'https://pcs.baidu.com/rest/2.0/pcs/thumbnail?method=generate&access_token='.$access_token.'&path='.$image_path.'&quality=100&width=1600&height=1600';
				$site_id = get_option('wp_to_pcs_site_id');
				$access_token = substr($access_token,0,10);
				$image_outlink = 'http://wp2pcs.duapp.com/img?'.$site_id.'+'.$access_token.'+path='.$image_path;
				header('Location:'.$image_outlink);
			}
			exit;
		}
	}
}