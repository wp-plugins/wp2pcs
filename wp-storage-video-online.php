<?php

// true强制采用外链，false则根据后台的设置来，视频采用m3u8格式输出，流量其实不大
// 由于百度网盘视频转码不是能解码所有文件，所以建议只使用avi/rm/mkv等主流视频格式，flv格式都有可能效果不佳
define('WP2PCS_VIDEO_HD',false);

// 创建一个函数，用来在wordpress中打印图片地址
function wp2pcs_video_src($video_path = false){
	// video_path是指相对于后台保存的存储目录的路径
	// 例如 $file_path = /test/test.avi
	// 注意最前面加/
	$vedio_perfix = get_option('wp_storage_to_pcs_video_perfix');
	$video_src = "/$vedio_perfix/".$video_path;
	$video_src = str_replace('//','/',$video_src);
	return home_url($video_src);
}

// 创建短代码来打印视频
function wp2pcs_video_shortcode($atts){
	extract(shortcode_atts(array(
		'src' => '',
		'cover' => '',
		'width' => '640',
		'height' => '480',
		'stretch' => 'bestfit'
	),$atts));

	$width = $width ? $width : '640';
	$height = $height ? $height : '480';
	$stretch = $stretch ? $stretch : 'bestfit';

	// 处理SRC中存在空格和中文的情况
	$src_arr = explode('/',$src);
	if(empty($src)){
		return;
	}
	foreach($src_arr as $key => $uri){
		if(preg_match('/[一-龥|\s]/u',$uri))$src_arr[$key] = rawurlencode($uri);
	}
	$src = implode('/',$src_arr);

	$player_id = get_php_run_time();
	$player = '<div id="videoplayer_'.$player_id.'" class="wp2pcs-video"></div><script type="text/javascript">var player=cyberplayer("videoplayer_'.$player_id.'").setup({width:'.$width.',height:'.$height.',backcolor:"#FFFFFF",stretching:"'.$stretch.'",file:"'.$src.'.m3u8",image:"'.$cover.'",autoStart:!1,repeat:"always",volume:100,controlbar:"over",ak:"CuOLkaVfoz1zGsqFKDgfvI0h",sk:"67kjwIh3wVLb5UYL"});</script>';

	return $player;
}
add_shortcode('video','wp2pcs_video_shortcode');

// 在网页头部输出音乐播放要使用到的javascript
add_action('wp_head','wp2pcs_video_player_script');
function wp2pcs_video_player_script(){
	// 如果你不打算让播放器出现在除了文章页之外的页面，如首页、列表页等，那么可以加上if(!is_singular())return;
	echo '<script type="text/javascript" src="http://cybertran.baidu.com/cloud/media/assets/cyberplayer/1.0/cyberplayer.min.js"></script>';
}

// 通过对URI的判断来获得图片远程信息
add_action('init','wp_storage_print_video',-1);
function wp_storage_print_video(){
	// 只用于前台使用视频
	if(is_admin()){
		return;
	}

	$current_uri = urldecode($_SERVER["REQUEST_URI"]);
	$video_perfix = trim(get_option('wp_storage_to_pcs_video_perfix'));
	$video_uri = $current_uri;
	$video_path = '';

	// 如果不存在前缀，就不执行了
	if(!$video_perfix){
		return;
	}

	// 获取安装在子目录
	$install_in_subdir = get_blog_install_in_subdir();

	// 由于百度云媒体播放器的安全策略，只有经过允许的域名才能正常播放视频，由于这个安全策略，必须在网站根目录放置crossdomain.xml
	$blog_root = ($install_in_subdir ? str_replace_last($install_in_subdir,'',ABSPATH) : ABSPATH);
	$crossdomain_file = $blog_root.'crossdomain.xml';
	if(!file_exists($crossdomain_file) && is_really_writable($blog_root)){
		copy(dirname(WP2PCS_PLUGIN_NAME).'/crossdomain.xml',$crossdomain_file);
	}

	// 判断路径后缀，如果不是.m3u8，就不往下执行
	if(substr($video_uri,-5) != '.m3u8'){
		return;
	}

	// 去除末尾的.m3u8，然后再判断对应的文件扩展名
	$video_uri = substr($video_uri,0,-5);
	$video_uri_ext = strtolower(substr($video_uri,strrpos($video_uri,'.')+1));
	if(!in_array($video_uri_ext,array('asf','avi','flv','mkv','mov','mp4','wmv','3gp','3g2','mpeg','ts','rm','rmvb'))){
		if(substr($video_uri,-5) == '.m3u8'){
			$video_uri = $current_uri;
		}else{
			return;
		}
	}

	// 当采用index.php/video时，大部分主机会跳转，丢失index.php，因此这里要做处理
	if(strpos($video_perfix,'index.php/')===0 && strpos($video_uri,'index.php/')===false){
		$video_perfix = str_replace_first('index.php/','',$video_perfix);
	}

	// 如果URI中根本不包含$video_perfix，那么就不用再往下执行了
	if(strpos($video_uri,$video_perfix)===false){
		return;
	}

	// 处理wordpress安装在子目录的情况
	if($install_in_subdir){
		$video_uri = str_replace_first($install_in_subdir,'',$video_uri);
	}

	// 返回真正有效的URI
	$video_uri = get_outlink_real_uri($video_uri,$video_perfix);

	// 如果URI中根本不包含$video_perfix，那么就不用再往下执行了
	if(strpos($video_uri,'/'.$video_perfix)!==0){
		return;
	}

	// 将前缀也去除，获取文件直接路径
	$video_path = str_replace_first('/'.$video_perfix,'',$video_uri);

	// 如果不存在video_path，也不执行了
	if(!$video_path){
		return;
	}

	// 获取视频路径
	$root_dir = get_option('wp_storage_to_pcs_root_dir');
	$video_path = trailingslashit($root_dir).$video_path;
	$video_path = str_replace('//','/',$video_path);
	$outlink_type = get_option('wp_storage_to_pcs_outlink_type');

	if($outlink_type == '200' && !WP2PCS_VIDEO_HD){
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
		// 打印视频m3u8到浏览器
		$pcs = new BaiduPCS(WP2PCS_APP_TOKEN);
		$result = $pcs->streaming($video_path,'M3U8_854_480');

		$meta = json_decode($result,true);
		if(isset($meta['error_msg'])){
			echo $meta['error_msg'];
			session_destroy();
			exit;
		}
		
		ob_clean();
		echo $result;
		session_destroy();
		exit;
	}else{
		$site_id = get_option('wp_to_pcs_site_id');
		$access_token = substr(WP2PCS_APP_TOKEN,0,10);
		$video_outlink = 'http://wp2pcs.duapp.com/v?'.$site_id.'+'.$access_token.'+path='.$video_path.'.m3u8';
		header('Location:'.$video_outlink);
		exit;
	}
	exit;
}