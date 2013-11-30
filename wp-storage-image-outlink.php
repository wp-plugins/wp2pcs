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
	// 只用于前台打印图片
	if(is_admin()){
		return;
	}
	$outlink_perfix = trim(get_option('wp_storage_to_pcs_outlink_perfix'));
	$current_uri = urldecode($_SERVER["REQUEST_URI"]);
	// 如果URI中根本不包含$outlink_perfix，那么就不用再往下执行了
	if(strpos($current_uri,$outlink_perfix) === false){
		return;
	}
	$uri_arr = array_values(array_filter(explode('/',$current_uri)));
	// 获取home_url其中的path部分，以此来判断是否安装在子目录中
	$install_in_sub_dir = parse_url(home_url(),PHP_URL_PATH);
	if($install_in_sub_dir){
		$home_dirs = array_filter(explode('/',$install_in_sub_dir));
		// 下面这个if将安装目录从URI中去除
		if(!empty($install_in_sub_dir))foreach($home_dirs as $dir){
			if($uri_arr[0] == $dir){
				array_shift($uri_arr);
			}else{
				return;
			}
		}
	}
	// 对于一些特殊的主机，重写规则会写成/index.php/uri，因此需要对此进行判断
	if($uri_arr[0] == 'index.php'){
		array_shift($uri_arr);
	}
	// 获取去除上述非有用URI后的第一个URI节，用来判断它是否等于$outlink_perfix
	if($outlink_perfix != $uri_arr[0]){
		return;
	}
	// 去除掉$outlink_perfix，为path做准备
	array_shift($uri_arr);
	// 获取图片路径
	$root_dir = get_option('wp_storage_to_pcs_root_dir');
	$image_path = trailingslashit($root_dir).implode('/',$uri_arr);
	$image_path = str_replace('//','/',$image_path);
	$outlink_type = get_option('wp_storage_to_pcs_outlink_type');

	if(isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],home_url()) !== 0){
		header("Content-Type: text/html; charset=utf-8");
		echo '防盗链！ ';
		echo '<a href="'.$current_uri.'">原图</a> ';
		echo '<a href="'.home_url('/').'">首页</a>';
		exit;
	}

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
		// 打印图片到浏览器
		$pcs = new BaiduPCS(WP2PCS_APP_TOKEN);
		$result = $pcs->downloadStream($image_path);
		ob_clean();
		header('Content-type: image/jpeg');
		echo $result;
	}else{
		$site_id = get_option('wp_to_pcs_site_id');
		$access_token = substr($access_token,0,10);
		$image_outlink = 'http://wp2pcs.duapp.com/img?'.$site_id.'+'.$access_token.'+path='.$image_path;
		header('Location:'.$image_outlink);
	}
	exit;
}