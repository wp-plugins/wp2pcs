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
	if(!is_admin() && !current_user_can('edit_theme_options'))return;
	if(is_multisite() && !current_user_can('manage_network')){
		return;
	}elseif(!current_user_can('edit_theme_options')){
		return;
	}
	// 替换图片路径
	if(!empty($_POST) && isset($_POST['page']) && $_POST['page'] == $_GET['page'] && isset($_POST['action']) && $_POST['action'] == 'wp_storage_to_pcs_replace_img_in_post' && 0){// 删除了图片路径替换功能
		global $wpdb;
		$img_url_base = get_option('wp_storage_to_pcs_image_perfix');
		$img_url_new_root = home_url('/'.$img_url_base.'/');
		$img_url_old_root = trim($_POST['wp_storage_to_pcs_replace_img_old_root']);
		if(!$img_url_old_root){
			wp_die('请认真填写老的图片目录！');
			exit;
		}
		$img_url_old_root = trailing_slash_path($img_url_old_root);
		update_option('wp_storage_to_pcs_replace_img_old_root',$img_url_old_root);
		$wpdb->query("UPDATE $wpdb->posts SET post_content=replace(post_content,'src=\"$img_url_old_root','src=\"$img_url_new_root')");
		$wpdb->query("UPDATE $wpdb->posts SET post_content=replace(post_content,'src=\'$img_url_old_root','src=\'$img_url_new_root')");
		wp_redirect(wp_to_pcs_wp_current_request_url(false).'?page='.$_GET['page'].'&time='.time().'#wp-to-pcs-storage-form');
		exit;
	}
	// 更新设置
	if(!empty($_POST) && isset($_POST['page']) && $_POST['page'] == $_GET['page'] && isset($_POST['action']) && $_POST['action'] == 'wp_storage_to_pcs_update'){
		check_admin_referer();
		$app_key = get_option('wp_to_pcs_app_key');
		// 更新备份到的目录
		$root_dir = trim($_POST['wp_storage_to_pcs_root_dir']);
		if(!$root_dir || empty($root_dir)){
			wp_die('请填写备份到网盘的目录！');
			exit;
		}
		if($app_key === 'false'){ // 托管在官方
			$root_dir = WP2PCS_SUB_DIR.$root_dir;
		}else{
			$root_dir = WP2PCS_ROOT_DIR.$root_dir;
		}
		$root_dir = trailing_slash_path($root_dir);
		update_option('wp_storage_to_pcs_root_dir',$root_dir);
		// 更新图片外链URL前缀
		$image_perfix = trim($_POST['wp_storage_to_pcs_image_perfix']);
		update_option('wp_storage_to_pcs_image_perfix',$image_perfix);
		/* 关闭了图片附件地址修改功能
		$image_hd = $_POST['wp_storage_to_pcs_image_hd'];
		if($image_hd)update_option('wp_storage_to_pcs_image_hd',$image_hd);
		else delete_option('wp_storage_to_pcs_image_hd');
		update_option('wp_storage_to_pcs_image_rb',$_POST['wp_storage_to_pcs_image_rb']);
		*/
		// 更新文件下载URL前缀
		$download_perfix = trim($_POST['wp_storage_to_pcs_download_perfix']);
		update_option('wp_storage_to_pcs_download_perfix',$download_perfix);
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
		// 防盗链
		$outlink_protact = $_POST['wp_storage_to_pcs_outlink_protact'];
		if($outlink_protact)update_option('wp_storage_to_pcs_outlink_protact',$outlink_protact);
		else delete_option('wp_storage_to_pcs_outlink_protact');
		// 更新外链形式
		$outlink_type = $_POST['wp_storage_to_pcs_outlink_type'];
		update_option('wp_storage_to_pcs_outlink_type',$outlink_type);
		wp_redirect(wp_to_pcs_wp_current_request_url(false).'?page='.$_GET['page'].'&time='.time().'#wp-to-pcs-storage-form');
		exit;
	}
}

// 下面是后台控制面板
function wp_storage_to_pcs_panel(){
	$app_key = get_option('wp_to_pcs_app_key');
	$root_dir = get_option('wp_storage_to_pcs_root_dir');
	$image_perfix = get_option('wp_storage_to_pcs_image_perfix');
	$download_perfix = get_option('wp_storage_to_pcs_download_perfix');
	$video_perfix = get_option('wp_storage_to_pcs_video_perfix');
	$audio_perfix = get_option('wp_storage_to_pcs_audio_perfix');
	$media_perfix = get_option('wp_storage_to_pcs_media_perfix');
	$outlink_type = get_option('wp_storage_to_pcs_outlink_type');
	$outlink_protact = get_option('wp_storage_to_pcs_outlink_protact');
	$img_url_old_root = get_option('wp_storage_to_pcs_replace_img_old_root');
	$image_hd = get_option('wp_storage_to_pcs_image_hd');
	$image_rb = get_option('wp_storage_to_pcs_image_rb');
	$video_hd = get_option('wp_storage_to_pcs_video_hd');
	$audio_hd = get_option('wp_storage_to_pcs_audio_hd');
	$media_hd = get_option('wp_storage_to_pcs_media_hd');
?>
<div class="postbox" id="wp-to-pcs-storage-form">
	<h3>PCS存储设置 <a href="javascript:void(0)" class="tishi-btn">+</a></h3>	
	<div class="inside" style="border-bottom:1px solid #CCC;margin:0;padding:8px 10px;">
	<form method="post">
		<p>使用网盘中的哪个目录：<?php if($app_key === 'false') : echo WP2PCS_SUB_DIR; ?><input type="text" name="wp_storage_to_pcs_root_dir"  class="regular-text" value="<?php echo str_replace(WP2PCS_SUB_DIR,'',$root_dir); ?>" /><?php else : echo WP2PCS_ROOT_DIR; ?><input type="text" name="wp_storage_to_pcs_root_dir" class="regular-text" value="<?php echo str_replace(WP2PCS_ROOT_DIR,'',$root_dir); ?>" /><?php endif; ?></p>
		<p class="tishi hidden">使用网盘中的某一个目录作为你存储图片或附件的根目录，例如你填写“/uploads/”，那么到时候就会采用这个目录下的文件作为附件。</p>
		<p>图片访问前缀：
			<input type="text" name="wp_storage_to_pcs_image_perfix" value="<?php echo $image_perfix; ?>" />
			<?php if(0): // 关闭图片附件访问功能 ?>
			<input type="checkbox" name="wp_storage_to_pcs_image_hd" value="true" <?php checked($image_hd,'true'); ?> />
			<select name="wp_storage_to_pcs_image_rb">
				<option value="1" <?php selected($image_rb,'1'); ?>>只替换所有图片链接</option>
				<option value="2" <?php selected($image_rb,'2'); ?>>特色图片链接和地址</option>
				<option value="3" <?php selected($image_rb,'3'); ?>>只替换所有图片地址</option>
				<option value="4" <?php selected($image_rb,'4'); ?>>所有图片链接和地址</option>
			</select>
			<a href="http://wp2pcs.duapp.com/286" title="本地图片访问URL会被转换为网盘中图片的访问URL，必须开启增量备份，设置的文件路径必须为特定的方式，必须开启上传时就同步，必须关闭“简易加速”！" target="_blank">必读：强制使用网盘图片注意事项?</a>
			<?php endif; ?>
		</p>
		<p class="tishi hidden">访问前缀是指用户访问你的网站的什么URL时才会调用网盘中的图片，例如你填写的是“img”，那么用户在访问“<?php echo home_url('/img/test.jpg'); ?>”时，屏幕上就会打印在你的网盘目录“/uploads/test.jpg”这张图片。为了提高不同空间的兼容性，建议你把这个前缀填写为“?img”的形式。</p>
		<p>下载访问前缀：<input type="text" name="wp_storage_to_pcs_download_perfix" value="<?php echo $download_perfix; ?>" /></p>
		<p>视频文件前缀：
			<input type="text" name="wp_storage_to_pcs_video_perfix" value="<?php echo $video_perfix; ?>" /> 
			<input type="checkbox" name="wp_storage_to_pcs_video_hd" value="true" <?php checked($video_hd,'true'); ?> /> 强制视频外链
			<a href="http://wp2pcs.duapp.com/198" title="使用说明" target="_blank">?</a>
		</p>
		<p>音频文件前缀：
			<input type="text" name="wp_storage_to_pcs_audio_perfix" value="<?php echo $audio_perfix; ?>" /> 
			<input type="checkbox" name="wp_storage_to_pcs_audio_hd" value="true" <?php checked($audio_hd,'true'); ?> /> 强制音乐外链
			<a href="http://wp2pcs.duapp.com/202" title="使用说明" target="_blank">?</a>
		</p>
		<p>流式文件前缀：
			<input type="text" name="wp_storage_to_pcs_media_perfix" value="<?php echo $media_perfix; ?>" /> 
			<input type="checkbox" name="wp_storage_to_pcs_media_hd" value="true" <?php checked($media_hd,'true'); ?> /> 强制流媒体外链
			<a href="http://wp2pcs.duapp.com/204" title="使用说明" target="_blank">?</a>
		</p>
		<p>附件访问方式：<select name="wp_storage_to_pcs_outlink_type">
			<option value="200" <?php selected($outlink_type,200); ?>>直链：耗流量，保护授权信息，利于SEO</option>
			<option value="302" <?php selected($outlink_type,302); ?>>外链：省流量，保护授权信息，SEO欠佳</option>
			<option value="301" <?php selected($outlink_type,301); ?>>外链：省流量，泄露授权信息，SEO欠佳</option>
		</select> <a href="http://wp2pcs.duapp.com/150" title="第三种方式是什么意思？" target="_blank">授权?</a></p>
		<P><input type="checkbox" name="wp_storage_to_pcs_outlink_protact" value="true" <?php checked($outlink_protact,'true'); ?> /> 防盗链</p>
		<p class="tishi hidden">防盗链功能：来自网站本身以外的其他访问都会被认为是盗链行为，当然如果你懂代码，可以通过修改插件源文件来扩大图片可用范围。</p>
		<p><input type="submit" value="确定" class="button-primary" /></p>
		<input type="hidden" name="action" value="wp_storage_to_pcs_update" />
		<input type="hidden" name="page" value="<?php echo $_GET['page']; ?>" />
		<?php wp_nonce_field(); ?>
	</form>
	</div>
	<?php if(0): // 关闭图片地址替换功能 ?>
	<div class="inside" style="border-bottom:1px solid #CCC;margin:0;padding:8px 10px;">
	<form method="post">
		<p class="tishi hidden"><strong>一键更新图片地址前缀功能</strong>：一键将图片从<?php echo home_url('/wp-content/uploads/2013/11/29/xxx.jpg'); ?>替换为<?php echo home_url('/'.$image_perfix.'/2013/11/29/xxx.jpg'); ?>。请看下面详细介绍。</p>
		<p>老的图片目录：<input type="text" name="wp_storage_to_pcs_replace_img_old_root" class="regular-text" value="<?php echo $img_url_old_root; ?>" /> <b style='color:#118508;'>-></b> <?php echo wp2pcs_image_src(); ?> <a href="http://wp2pcs.duapp.com/160" target="_blank" title="一键替换功能的原理与使用方法">?</a></p>
		<p class="tishi hidden">一键替换：1、你准备把以前存放在网站空间里面的所有<span style="color:red;">图片</a>转移到百度网盘，首先使用ftp等工具先把所有图片下载到本地，一般而言，你直接下载/wp-content/uploads/目录即可，下载完成之后打开uploads目录，把里面的文件上传到百度网盘中存放附件的目录下<?php echo ($root_dir ? "($root_dir)" : ''); ?>，然后在上面填写<?php echo home_url('/wp-content/uploads/'); ?>，点击提交即可。不过有的博客不是WP默认的存储路径，这个时候你必须根据实际情况来确定。2、当你本来使用img作为图片访问前缀，而现在修改为image作为前缀，那么你需要使用这个功能调整文章中的图片地址，否则图片将无法被访问到。如果你的图片存在多种老的路径，可以多次提交，实现最终统一，但在这个过程中一定要注意不要造成覆盖从而引起错误，如果你不能自己完成这项工作，可以<a href="http://wp2pcs.duapp.com/160" target="_blank">获取WP2PCS官方提供的高级解决方案</a>。</p>
		<p><input type="submit" value="一键替换" onclick="if(!confirm('是否已经备份数据库？请一定要理解该功能的替换原理后再来使用，否则可能造成图片无法显示。')){return false;}if(confirm('WP2PCS官方提供了更为高级的解决方案，点击确认进行了解，点击取消继续')){window.open('http://wp2pcs.duapp.com/160');return false;}" class="button-primary" /></p>
		<input type="hidden" name="action" value="wp_storage_to_pcs_replace_img_in_post" />
		<input type="hidden" name="page" value="<?php echo $_GET['page']; ?>" />
		<?php wp_nonce_field(); ?>
	</form>
	</div>
	<?php endif; ?>
	<div class="inside tishi hidden" style="border-bottom:1px solid #CCC;margin:0;padding:8px 10px;">
		<p class="tishi hidden">你还需要注意一些兼容性问题。这不是指插件本身的问题，而是指与其他环境的冲突，例如你使用了CDN缓存服务，就有可能造成图片缓存而不能被访问；如果你使用了其他插件来优化你的图片URL，也最好将这些插件重新设计。</p>
	</div>
</div>
<?php
}