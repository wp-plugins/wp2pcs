<?php

/*
*
* # 实现把百度网盘作为网站的附件存储工具
* # 可以实现网盘文件的外链
* # 为了减轻网站本身的压力，本工具规定用户自己先将图片上传到网盘，本地使用时提供一个对话框，站长可以看到自己网盘上已经有的图片，选择某一个图片作为外链
* # 图片也指附件，其他附件就不提供直接外链，而提供下载地址
*
*/


// 提交控制面板中的信息时
add_action('admin_init','wp_storage_to_pcs_action');
function wp_storage_to_pcs_action(){
	if(is_multisite() && !current_user_can('manage_network')){
		return;
	}elseif(!current_user_can('edit_theme_options')){
		return;
	}
	// 更新设置
	if(!empty($_POST) && isset($_POST['page']) && $_POST['page'] == $_GET['page'] && isset($_POST['action']) && $_POST['action'] == 'wp_storage_to_pcs_update'){
		check_admin_referer();
		// 更新备份到的目录
		$remote_dir = trim($_POST['wp_storage_to_pcs_remote_dir']);
		if(!$remote_dir || empty($remote_dir)){
			wp_die('请填写附件在网盘中的存储目录！');
			exit;
		}
		$remote_dir =  WP2PCS_REMOTE_ROOT.trailing_slash_path($remote_dir);
		update_option('wp_storage_to_pcs_remote_dir',$remote_dir);
		// 更新图片外链URL前缀
		$image_perfix = trim($_POST['wp_storage_to_pcs_image_perfix']);
		update_option('wp_storage_to_pcs_image_perfix',$image_perfix);
		$image_hd = $_POST['wp_storage_to_pcs_image_hd'];
		if($image_hd)update_option('wp_storage_to_pcs_image_hd',$image_hd);
		else delete_option('wp_storage_to_pcs_image_hd');
		// 更新文件下载URL前缀
		$download_perfix = trim($_POST['wp_storage_to_pcs_download_perfix']);
		update_option('wp_storage_to_pcs_download_perfix',$download_perfix);
		$download_hd = $_POST['wp_storage_to_pcs_download_hd'];
		if($download_hd)update_option('wp_storage_to_pcs_download_hd',$download_hd);
		else delete_option('wp_storage_to_pcs_download_hd');
		// 更新视频
		$video_perfix = trim($_POST['wp_storage_to_pcs_video_perfix']);
		update_option('wp_storage_to_pcs_video_perfix',$video_perfix);
		$video_hd = $_POST['wp_storage_to_pcs_video_hd'];
		if($video_hd)update_option('wp_storage_to_pcs_video_hd',$video_hd);
		else delete_option('wp_storage_to_pcs_video_hd');
		// 更新音乐
		$audio_perfix = trim($_POST['wp_storage_to_pcs_audio_perfix']);
		update_option('wp_storage_to_pcs_audio_perfix',$audio_perfix);
		$audio_hd = $_POST['wp_storage_to_pcs_audio_hd'];
		if($audio_hd)update_option('wp_storage_to_pcs_audio_hd',$audio_hd);
		else delete_option('wp_storage_to_pcs_audio_hd');
		// 更新流媒体
		$media_perfix = trim($_POST['wp_storage_to_pcs_media_perfix']);
		update_option('wp_storage_to_pcs_media_perfix',$media_perfix);
		$media_hd = $_POST['wp_storage_to_pcs_media_hd'];
		if($media_hd)update_option('wp_storage_to_pcs_media_hd',$media_hd);
		else delete_option('wp_storage_to_pcs_media_hd');
		// 完成，跳转
		wp_redirect(wp_to_pcs_wp_current_request_url(false).'?page='.$_GET['page'].'&time='.time().'#wp-to-pcs-storage-form');
		exit;
	}
}

// 下面是后台控制面板
function wp_storage_to_pcs_panel(){
	$remote_dir = get_option('wp_storage_to_pcs_remote_dir');
	// 前缀
	$image_perfix = get_option('wp_storage_to_pcs_image_perfix');
	$download_perfix = get_option('wp_storage_to_pcs_download_perfix');
	$video_perfix = get_option('wp_storage_to_pcs_video_perfix');
	$audio_perfix = get_option('wp_storage_to_pcs_audio_perfix');
	$media_perfix = get_option('wp_storage_to_pcs_media_perfix');
	// 外链
	$image_hd = get_option('wp_storage_to_pcs_image_hd');
	$download_hd = get_option('wp_storage_to_pcs_download_hd');
	$video_hd = get_option('wp_storage_to_pcs_video_hd');
	$audio_hd = get_option('wp_storage_to_pcs_audio_hd');
	$media_hd = get_option('wp_storage_to_pcs_media_hd');
?>
<div class="postbox" id="wp-to-pcs-storage-form">
	<h3>PCS存储设置 <a href="javascript:void(0)" class="tishi-btn">+</a></h3>	
	<div class="inside" style="border-bottom:1px solid #CCC;margin:0;padding:8px 10px;">
	<form method="post">
		<p>使用网盘中的哪个目录：<?php echo WP2PCS_REMOTE_ROOT; ?><input type="text" name="wp_storage_to_pcs_remote_dir"  class="regular-text" value="<?php echo str_replace(WP2PCS_REMOTE_ROOT,'',$remote_dir); ?>" /></p>
		<p class="tishi hidden">使用网盘中的某一个目录作为你存储图片或附件的根目录，例如你填写“uploads”，那么到时候就会采用这个目录下的文件作为附件。</p>
		<p>图片访问前缀：
			<input type="text" name="wp_storage_to_pcs_image_perfix" value="<?php echo $image_perfix; ?>" />
			<input type="checkbox" name="wp_storage_to_pcs_image_hd" value="301" <?php checked($image_hd,'301'); ?> />
			外链 <a href="http://wp2pcs.duapp.com/286" title="使用说明" target="_blank">?</a>
		</p>
		<p class="tishi hidden">访问前缀是指用户访问你的网站的什么URL时才会调用网盘中的图片，例如你填写的是“img”，那么用户在访问“<?php echo home_url('/img/test.jpg'); ?>”时，屏幕上就会打印在你的网盘目录“<?php echo WP2PCS_REMOTE_ROOT; ?>uploads/test.jpg”这张图片。为了提高不同空间的兼容性，默认为“?img”的形式。</p>
		<p class="tishi hidden">下载访问前缀：
			<input type="text" name="wp_storage_to_pcs_download_perfix" value="<?php echo $download_perfix; ?>" />
			<input type="checkbox" name="wp_storage_to_pcs_download_hd" value="301" <?php checked($download_hd,'301'); ?> />
			外链 <a href="http://wp2pcs.duapp.com/286" title="使用说明" target="_blank">?</a>
		</p>
		<p class="tishi hidden">视频访问前缀：
			<input type="text" name="wp_storage_to_pcs_video_perfix" value="<?php echo $video_perfix; ?>" /> 
		</p>
		<p class="tishi hidden">MP3访问前缀：
			<input type="text" name="wp_storage_to_pcs_audio_perfix" value="<?php echo $audio_perfix; ?>" /> 
			<input type="checkbox" name="wp_storage_to_pcs_audio_hd" value="301" <?php checked($audio_hd,'301'); ?> />
			外链 <a href="http://wp2pcs.duapp.com/202" title="使用说明" target="_blank">?</a>
		</p>
		<p>文件访问前缀：
			<input type="text" name="wp_storage_to_pcs_media_perfix" value="<?php echo $media_perfix; ?>" /> 
			<input type="checkbox" name="wp_storage_to_pcs_media_hd" value="301" <?php checked($media_hd,'301'); ?> />
			外链 <a href="http://wp2pcs.duapp.com/204" title="使用说明" target="_blank">?</a>
		</p>
		<p><input type="submit" value="确定" class="button-primary" /></p>
		<input type="hidden" name="action" value="wp_storage_to_pcs_update" />
		<input type="hidden" name="page" value="<?php echo $_GET['page']; ?>" />
		<?php wp_nonce_field(); ?>
	</form>
	</div>
	<div class="inside tishi hidden" style="border-bottom:1px solid #CCC;margin:0;padding:8px 10px;">
		<p>你还需要注意一些兼容性问题。这不是指插件本身的问题，而是指与其他环境的冲突，例如你使用了CDN缓存服务，就有可能造成图片缓存而不能被访问；如果你使用了其他插件来优化你的图片URL，也最好将这些插件重新设计。</p>
		<p style="color:red;">如果你在使用中遇到问题，随时<a href="http://www.wp2pcs.com/?cat=6" target="_blank">申请帮助</a>，以获得VIP专享服务。</p>
	</div>
</div>
<?php
}