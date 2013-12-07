<?php

// true强制采用外链，false则根据后台的设置来，音乐会消耗大量流量，且受网速影响
// 但另外一个问题是，如果音乐文件太大，则外链受到BAE的影响，会泄露token信息，故不建议使用超过10M的外链音乐（直链没有token问题）
// 由于百度网盘对音乐文件的解码也不怎么好，故建议只使用mp3格式音乐文件
define('WP2PCS_AUDIO_HD',false);

// 创建一个函数，用来在wordpress中打印图片地址
function wp2pcs_audio_src($audio_path){
	// audio_path是指相对于后台保存的存储目录的路径
	// 例如 $file_path = /test/test.avi
	// 注意最前面加/
	$audio_perfix = get_option('wp_storage_to_pcs_audio_perfix');
	$audio_src = "/$audio_perfix/".$audio_path;
	$audio_src = str_replace('//','/',$audio_src);
	return home_url($audio_src);
}

// 通过对URI的判断来获得图片远程信息
add_action('init','wp_storage_print_audio',-1);
function wp_storage_print_audio(){
	// 只用于前台使用音乐
	if(is_admin()){
		return;
	}

	$current_uri = urldecode($_SERVER["REQUEST_URI"]);
	$audio_perfix = get_option('wp_storage_to_pcs_audio_perfix');
	$audio_uri = $current_uri;
	$audio_path = '';

	// 如果不存在前缀，就不执行了
	if(!$audio_perfix){
		return;
	}

	// 获取文件扩展名
	$file_ext = strtolower(substr($audio_uri,strrpos($audio_uri,'.')+1));
	if(!in_array($file_ext,array('ogg','mp3','wma','wav','mp3pro','ape','module','midi','vqf'))){
		return;
	}

	// 当采用index.php/audio时，大部分主机会跳转，丢失index.php，因此这里要做处理
	if(strpos($audio_perfix,'index.php/')===0 && strpos($audio_uri,'index.php/')===false){
		$audio_perfix = str_replace_first('index.php/','',$audio_perfix);
	}

	// 如果URI中根本不包含$audio_perfix，那么就不用再往下执行了
	if(strpos($audio_uri,$audio_perfix)===false){
		return;
	}

	// 获取安装在子目录
	$install_in_subdir = get_blog_install_in_subdir();
	if($install_in_subdir){
		$audio_uri = str_replace_first($install_in_subdir,'',$audio_uri);
	}

	// 如果在IIS上面
	if(get_blog_install_software() == 'IIS'){
		if(
			(strpos($audio_uri,'/index.php/')===0 
			&& strpos($audio_perfix,'index.php/')!==0
			&& strpos($audio_uri,'/index.php/'.$audio_perfix)===0)
			||
			(strpos($audio_uri,'/index.php/index.php/')===0 
			&& strpos($audio_perfix,'index.php/')===0
			&& strpos($audio_uri,'/index.php/'.$audio_perfix)===0)
		){
			$audio_uri = str_replace_first('/index.php','',$audio_uri);	
		}
	}

	// 如果URI中根本不包含$audio_perfix，那么就不用再往下执行了
	if(strpos($audio_uri,'/'.$audio_perfix)!==0){
		return;
	}
	
	// 将前缀也去除，获取文件直接路径
	$audio_path = str_replace_first('/'.$audio_perfix,'',$audio_uri);

	// 如果不存在audio_path，也不执行了
	if(!$audio_path){
		return;
	}

	// 获取视频路径
	$root_dir = get_option('wp_storage_to_pcs_root_dir');
	$audio_path = trailingslashit($root_dir).$audio_path;
	$audio_path = str_replace('//','/',$audio_path);
	// 获取外链方式
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
			exit;
		}
		// 打印音乐到浏览器
		$pcs = new BaiduPCS(WP2PCS_APP_TOKEN);
		$result = $pcs->downloadStream($audio_path);
		
		$meta = json_decode($result,true);
		if(isset($meta['error_msg'])){
			echo $meta['error_msg'];
			exit;
		}

		header("Content-Type: audio/mpeg");
		header('Content-Disposition: inline; filename="'.basename($audio_path).'"');
		header("Content-Transfer-Encoding: binary");
		header('Content-length: '.strlen($result));
		ob_clean();
		echo $result;
	}else{
		$site_id = get_option('wp_to_pcs_site_id');
		$access_token = substr(WP2PCS_APP_TOKEN,0,10);
		$audio_outlink = 'http://wp2pcs.duapp.com/music?'.$site_id.'+'.$access_token.'+path='.$audio_path;
		header('Location:'.$audio_outlink);
	}
	exit;
}