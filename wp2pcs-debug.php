<?php

/*
 * 这个文件专门为调试WP2PCS准备，如果你的网站在使用WP2PCS中存在什么问题，那么修改wp2pcs.php中WP2PCS_DEBUG为true即可知道具体是什么问题了。
 */


	// 只在前台进行调试，否则连后台都进不去了
	if(is_admin()){
		return;
	}

	// 显示运行错误
	error_reporting(E_ALL);

	// 输出文字
	header("Content-Type: text/html; charset=utf-8");
	
	// 测试session是否可以用
	session_start();
	echo "如果在这句话之前没有看到错误，说明session可以正常使用<br />";
	session_destroy();
	
	// 首先检查php环境
	echo "你的网站搭建在 ".PHP_OS." 操作系统的服务器上<br />";
	$software = get_blog_install_software();
	echo "你的网站运行在 $software 服务器上，不同的服务器重写功能会对插件的运行有影响<br />";
	echo "当前的php版本为 ".PHP_VERSION."<br />";

	// 检查是否支持重写功能
	$permalink_structure = get_option('permalink_structure');
	$software = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '';
	if(strpos($software,'IIS') !== false){
		$software = 'IIS';
	}elseif(strpos($software,'Apache') !== false){
		$software = 'Apache';
	}elseif(strpos($software,'NginX') !== false){
		$software = 'NginX';
	}else{
		$software = 'Others';
	}
	$install_root = ABSPATH;
	$install_in_subdir = get_blog_install_in_subdir();
	if($software == 'IIS' && $install_in_subdir){
		$install_root = str_replace_last($install_in_subdir.'/','',$install_root);
	}

	if($permalink_structure){
		echo "你已经修改了固定链接形式 $permalink_structure  ，先关闭调试模式，<a href='".home_url('/?p=1')."' target='_blank'>随意阅读一篇文章</a>，看看是否能够被正常访问<br />";
	}else{
		echo "你尚没有修改固定链接形式，插件后台图片等访问前缀不能修改为 image 等形式， ?image 这种形式则可以<br />";
	}
	if(file_exists($install_root.'httpd.ini')){
		echo "你的网站中使用了httpd.ini<br />";
	}
	elseif(file_exists($install_root.'.htaccess')){
		echo "你的网站中使用了.htaccess<br />";
	}
	elseif(file_exists($install_root.'httpd.conf')){
		echo "你的网站中使用了httpd.conf<br />";
	}
	elseif(file_exists($install_root.'app.conf')){
		echo "你的网站中使用了app.conf<br />";
	}
	elseif(file_exists($install_root.'config.yaml')){
		echo "你的网站中使用了config.yaml<br />";
	}
	else{
		echo "没有发现和重写相关的配置文件<br />";
	}

	// 检查content目录的写入权限
	if(DIRECTORY_SEPARATOR=='/' && @ini_get("safe_mode")==FALSE){
		echo "没有开启安全模式，".(is_writable(WP_CONTENT_DIR) ? 'content目录可写' : 'content目录不可写')."<br />";
	}else{
		echo "开启了安全模式，";
		$file = rtrim(WP_CONTENT_DIR,'/').'/'.md5(mt_rand(1,100).mt_rand(1,100));
		if(($fp = @fopen($file,'w+'))===FALSE){
			echo "content目录不可写";
		}else{
			echo "content目录可写";
		}
		fclose($fp);
		@chmod($file,'0755');
		@unlink($file);
		echo "<br />";
	}

	// 检查是否存在crossdomain.xml
	$domain_root = $install_root;
	if($install_in_subdir){
		$domain_root = str_replace_last($install_in_subdir.'/','',$install_root);
	}
	if(file_exists($domain_root.'crossdomain.xml')){
		echo "存在crossdomain.xml，<a href='http://".$_SERVER['SERVER_NAME']."/crossdomain.xml' target='_blank'>检查一下它是否可以被正常访问</a>，并显示出xml结果<br />";
	}else{
		echo "不存在crossdomain.xml文件，网盘中的视频将不能被正常播放<br />";
	}

	// 测试创建文件及其相关
	$file = trailingslashit(WP_CONTENT_DIR).'wp2pcs-debug.txt';
	$handle = fopen($file,"w+");
	$words_count = fwrite($handle,'你的服务器支持创建和写入文件');
	if($words_count > 0){
		echo "创建和写入文件成功，你的服务器支持文件创建和写入<br />";
	}
	$file_content = fread($handle,10);
	$read_over = feof($handle);
	if($file_content){
		echo "读取文件成功，你的服务器支持文件读取<br />";
		echo "读取结果为 $read_over ";
	}
	fclose($handle);
	unlink($file);

	// 检查是否授权通过
	$pcs = new BaiduPCS(WP2PCS_APP_TOKEN);
	$quota = json_decode($pcs->getQuota());
	if(!$pcs || !$quota || isset($quota->error_code)){
		echo '授权失败，也有可能是因为你的主机和百度PCS服务器通信失败';
	}else{
		echo '百度PCS授权成功';
	}

	echo "<br /><br />目前该测试文件只在linux appache上通过测试，如果你使用的是win主机，或者其他主机，请与我联系。<br /><br />";

	/*
	 * 查看图片调试结果
	 */
	$image_perfix = get_option('wp_storage_to_pcs_image_perfix');
	$audio_perfix = get_option('wp_storage_to_pcs_audio_perfix');
	$video_perfix = get_option('wp_storage_to_pcs_video_perfix');
	$media_perfix = get_option('wp_storage_to_pcs_media_perfix');
	$download_perfix = get_option('wp_storage_to_pcs_download_perfix');

	echo "图片前缀： $image_perfix <br />";
	echo "音乐前缀： $audio_perfix <br />";
	echo "视频前缀： $audio_perfix <br />";
	echo "媒体前缀： $media_perfix <br />";
	echo "下载前缀： $download_perfix <br />";
	
	$image_link = home_url('/'.$image_perfix.'/test.jpg');
	echo "<a href='$image_link'>点击查看图片调试结果<a><br />";
	
	$image_perfix = get_option('wp_storage_to_pcs_image_perfix');
	$current_uri = urldecode($_SERVER["REQUEST_URI"]);
	$image_uri = $current_uri;
	$image_path = '';

	echo "正常访问，下面开始测试图片：<br />";
	echo "1.当前的URI为 $current_uri <br />";

	// 如果不存在前缀，就不执行了
	if(!$image_perfix){
		echo "当前插件配置中图片前缀没有填写";
		exit;
	}

	echo "2.前缀设置正常 $image_perfix <br />";

	// 当采用index.php/image时，大部分主机会跳转，丢失index.php，因此这里要做处理
	if(strpos($image_perfix,'index.php/')===0 && strpos($image_uri,'index.php/')===false){
		$image_perfix = str_replace_first('index.php/','',$image_perfix);
	}
	
	// 如果URI中根本不包含$image_perfix，那么就不用再往下执行了
	if(strpos($image_uri,$image_perfix)===false){
		echo "当前URL中不存在图片访问前缀";
		exit;
	}

	echo "3.当前的IMAGE URI为 $image_uri ，包含 $image_perfix <br />";

	// 获取安装在子目录
	$install_in_subdir = get_blog_install_in_subdir();
	if($install_in_subdir){
		$image_uri = str_replace_first($install_in_subdir,'',$image_uri);
	}

	if($install_in_subdir)echo "4.wordpress被安装在子目录 $install_in_subdir 中<br />";
	echo "当前的IMAGE URI为 $image_uri<br />";

	// 如果在IIS上面
	if(get_blog_install_software() == 'IIS'){
		if(
			(strpos($image_uri,'/index.php/')===0 
			&& strpos($image_perfix,'index.php/')!==0
			&& strpos($image_uri,'/index.php/'.$image_perfix)===0)
			||
			(strpos($image_uri,'/index.php/index.php/')===0 
			&& strpos($image_perfix,'index.php/')===0
			&& strpos($image_uri,'/index.php/'.$image_perfix)===0)
		){
			$image_uri = str_replace_first('/index.php','',$image_uri);	
		}
		echo "5.当前服务器软件为IIS<br />";
		echo "当前的IMAGE URI为 $image_uri<br />";
	}

	// 如果URI中根本不包含$image_perfix，那么就不用再往下执行了
	if(strpos($image_uri,'/'.$image_perfix)!==0){
		echo "经过计算之后，当前的URL中不存在图片访问前缀";
		exit;
	}

	// 将前缀也去除，获取文件直接路径
	$image_path = str_replace_first('/'.$image_perfix,'',$image_uri);

	echo "如果没有看到5，说明不是安装在IIS上的<br />";
	echo "6.当前的IMAGE URI为 $image_uri 获取到的文件路径为 $image_path<br />";

	// 如果不存在image_path，也不执行了
	if(!$image_path){
		echo "图片地址不能为空";
		exit;
	}

	// 获取图片路径
	$root_dir = get_option('wp_storage_to_pcs_root_dir');
	$image_path = trailingslashit($root_dir).$image_path;
	$image_path = str_replace('//','/',$image_path);
	$outlink_type = get_option('wp_storage_to_pcs_outlink_type');
	if($outlink_type == 200){
		$outlink_type = "直链";
	}elseif($outlink_type == 301){
		$outlink_type = "保护授权信息的外链方式，只有在开发者版本中才使用";
	}else{
		$outlink_type = "外链";
	}

	echo "7.图片最终路径为 $image_path ，附件访问方式为： $outlink_type <br />";
	echo "如果你能看到这里，说明你的图片（也包括其他附件）应该是可以正常显示的。<br />";

	// 结束调试
	exit;