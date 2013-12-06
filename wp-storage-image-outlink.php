<?php

// 创建一个函数，用来在wordpress中打印图片地址
function wp2pcs_image_src($image_path){
	// image_path是指相对于后台保存的存储目录的路径
	// 例如 $image_path = /test/test.jpg
	// 注意最前面加/
	$image_perfix = trim(get_option('wp_storage_to_pcs_image_perfix'));
	$image_src = "/$image_perfix/".$image_path;
	$image_src = str_replace('//','/',$image_src);
	return home_url($image_src);
}

// 通过对URI的判断来获得图片远程信息
add_action('init','wp_storage_print_image',-1);
function wp_storage_print_image(){
	// 只用于前台打印图片
	if(is_admin()){
		return;
	}

	$image_perfix = get_option('wp_storage_to_pcs_image_perfix');
	$current_uri = urldecode($_SERVER["REQUEST_URI"]);
	$image_uri = $current_uri;
	$image_path = '';

	// 如果不存在前缀，就不执行了
	if(!$image_perfix){
		return;
	}

	// 当采用index.php/image时，大部分主机会跳转，丢失index.php，因此这里要做处理
	if(strpos($image_perfix,'index.php/')===0 && strpos($image_uri,'index.php/')===false){
		$image_perfix = str_replace_first('index.php/','',$image_perfix);
	}
	
	// 如果URI中根本不包含$image_perfix，那么就不用再往下执行了
	if(strpos($image_uri,$image_perfix)===false){
		return;
	}

	// 获取安装在子目录
	$install_in_subdir = get_blog_install_in_subdir();
	if($install_in_subdir){
		$image_uri = str_replace_first($install_in_subdir,'',$image_uri);
	}

	// 如果在IIS上面
	if(get_blog_install_on_iis()){
		if(strpos($image_uri,'/index.php/')!==0){
			return;
		}
		if(strpos($image_perfix,'index.php/')===0 && strpos($image_uri,'/index.php/'.$image_perfix)!==0){
			return;
		}
		$image_uri = str_replace_first('/index.php','',$image_uri);		
	}

	// 如果URI中根本不包含$image_perfix，那么就不用再往下执行了
	if(strpos($image_uri,'/'.$image_perfix)!==0){
		return;
	}
	
	// 将前缀也去除，获取文件直接路径
	$image_path = str_replace_first('/'.$image_perfix,'',$image_uri);

	// 如果不存在image_path，也不执行了
	if(!$image_path){
		return;
	}

	// 获取图片路径
	$root_dir = get_option('wp_storage_to_pcs_root_dir');
	$image_path = trailingslashit($root_dir).$image_path;
	$image_path = str_replace('//','/',$image_path);
	$outlink_type = get_option('wp_storage_to_pcs_outlink_type');

	// 防盗链
	if(isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],home_url())!==0 && get_option('wp_storage_to_pcs_outlink_protact')){
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
		header('Content-type: image/jpeg');
		ob_clean();
		echo $result;
	}else{
		$site_id = get_option('wp_to_pcs_site_id');
		$access_token = substr(WP2PCS_APP_TOKEN,0,10);
		$image_outlink = 'http://wp2pcs.duapp.com/img?'.$site_id.'+'.$access_token.'+path='.$image_path;
		header('Location:'.$image_outlink);
	}
	exit;
}