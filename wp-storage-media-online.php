<?php

// true强制采用外链，false则根据后台的设置来，媒体会消耗大量流量，且受网速影响
// 但另外一个问题是，如果媒体文件太大，则外链受到BAE的影响，会泄露token信息，故不建议使用超过10M的外链媒体（直链没有token问题）
define('WP2PCS_AUDIO_HD',false);

// 创建一个函数，用来在wordpress中打印图片地址
function wp2pcs_media_src($media_path){
	// media_path是指相对于后台保存的存储目录的路径
	// 例如 $file_path = /test/test.avi
	// 注意最前面加/
	$media_perfix = get_option('wp_storage_to_pcs_media_perfix');
	$media_src = "/$media_perfix/".$media_path;
	$media_src = str_replace('//','/',$media_src);
	return home_url($media_src);
}

// 通过对URI的判断来获得图片远程信息
add_action('init','wp_storage_print_media',-1);
function wp_storage_print_media(){
	// 只用于前台使用媒体
	if(is_admin()){
		return;
	}

	$current_uri = urldecode($_SERVER["REQUEST_URI"]);
	$media_perfix = get_option('wp_storage_to_pcs_media_perfix');
	$media_uri = $current_uri;
	$media_path = '';

	// 如果不存在前缀，就不执行了
	if(!$media_perfix){
		return;
	}

	// 当采用index.php/media时，大部分主机会跳转，丢失index.php，因此这里要做处理
	if(strpos($media_perfix,'index.php/')===0 && strpos($media_uri,'index.php/')===false){
		$media_perfix = str_replace_first('index.php/','',$media_perfix);
	}

	// 如果URI中根本不包含$media_perfix，那么就不用再往下执行了
	if(strpos($media_uri,$media_perfix)===false){
		return;
	}

	// 获取安装在子目录
	$install_in_subdir = get_blog_install_in_subdir();
	if($install_in_subdir){
		$media_uri = str_replace_first($install_in_subdir,'',$media_uri);
	}

	// 如果在IIS上面
	if(get_blog_install_software() == 'IIS'){
		if(
			(strpos($media_uri,'/index.php/')===0 
			&& strpos($media_perfix,'index.php/')!==0
			&& strpos($media_uri,'/index.php/'.$media_perfix)===0)
			||
			(strpos($media_uri,'/index.php/index.php/')===0 
			&& strpos($media_perfix,'index.php/')===0
			&& strpos($media_uri,'/index.php/'.$media_perfix)===0)
		){
			$media_uri = str_replace_first('/index.php','',$media_uri);	
		}
	}

	// 如果URI中根本不包含$media_perfix，那么就不用再往下执行了
	if(strpos($media_uri,'/'.$media_perfix)!==0){
		return;
	}
	
	// 将前缀也去除，获取文件直接路径
	$media_path = str_replace_first('/'.$media_perfix,'',$media_uri);

	// 如果不存在media_path，也不执行了
	if(!$media_path){
		return;
	}

	// 获取媒体路径
	$root_dir = get_option('wp_storage_to_pcs_root_dir');
	$media_path = trailingslashit($root_dir).$media_path;
	$media_path = str_replace('//','/',$media_path);
	$outlink_type = get_option('wp_storage_to_pcs_outlink_type');

	if($outlink_type == '200' && !WP2PCS_AUDIO_HD){
		// 考虑到流量问题，必须增加缓存能力
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
		// 输出流文件
		$pcs = new BaiduPCS(WP2PCS_APP_TOKEN);
		$result = $pcs->downloadStream($media_path);
				
		$meta = json_decode($result,true);
		if(isset($meta['error_msg'])){
			echo $meta['error_msg'];
			session_destroy();
			exit;
		}
		
		header("Content-Type: application/octet-stream");
		header('Content-Disposition:inline;filename="'.basename($media_path).'"');
		header('Accept-Ranges: bytes');

		ob_clean();
		echo $result;
		session_destroy();
		exit;
	}else{
		$site_id = get_option('wp_to_pcs_site_id');
		$access_token = substr(WP2PCS_APP_TOKEN,0,10);
		$media_outlink = 'http://wp2pcs.duapp.com/media?'.$site_id.'+'.$access_token.'+path='.$media_path;
		header('Location:'.$media_outlink);
	}
	exit;
}