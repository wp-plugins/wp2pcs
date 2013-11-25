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
add_action('init','wp_storage_to_pcs_action');
function wp_storage_to_pcs_action(){
	if(!is_admin() && !current_user_can('edit_theme_options'))return;
	if(is_multisite() && !current_user_can('manage_network')){
		return;
	}elseif(!current_user_can('edit_theme_options')){
		return;
	}
	if(!empty($_POST) && isset($_POST['page']) && $_POST['page'] == $_GET['page'] && isset($_POST['action']) && $_POST['action'] == 'wp_storage_to_pcs_update'){
		check_admin_referer();
		$app_key = get_option('wp_to_pcs_app_key');
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
		$root_dir = trailingslashit($root_dir);
		update_option('wp_storage_to_pcs_root_dir',$root_dir);
		$outlink_perfix = trim($_POST['wp_storage_to_pcs_outlink_perfix']);
		update_option('wp_storage_to_pcs_outlink_perfix',$outlink_perfix);
		$download_perfix = trim($_POST['wp_storage_to_pcs_download_perfix']);
		update_option('wp_storage_to_pcs_download_perfix',$download_perfix);
		if(isset($_POST['wp_storage_to_pcs_outlink_type'])){
			$outlink_type = $_POST['wp_storage_to_pcs_outlink_type'];
			update_option('wp_storage_to_pcs_outlink_type',$outlink_type);
		}
		wp_redirect(remove_query_arg('_wpnonce',add_query_arg(array('time'=>time()))));
		exit;
	}
}

// 下面是后台控制面板
function wp_storage_to_pcs_panel(){
	$app_key = get_option('wp_to_pcs_app_key');
	$root_dir = get_option('wp_storage_to_pcs_root_dir');
	$outlink_perfix = get_option('wp_storage_to_pcs_outlink_perfix');
	$download_perfix = get_option('wp_storage_to_pcs_download_perfix');
	$outlink_type = get_option('wp_storage_to_pcs_outlink_type');
?>
<div class="postbox">
	<h3>PCS存储设置</h3>
	<form method="post">
	<div class="inside" style="border-bottom:1px solid #CCC;margin:0;padding:8px 10px;">
		<p>使用网盘中的哪个目录：
		<?php if($app_key === 'false') : echo WP2PCS_SUB_DIR; ?><input type="text" name="wp_storage_to_pcs_root_dir"  class="regular-text" value="<?php echo str_replace(WP2PCS_SUB_DIR,'',$root_dir); ?>" /><?php else : echo WP2PCS_ROOT_DIR; ?><input type="text" name="wp_storage_to_pcs_root_dir" class="regular-text" value="<?php echo str_replace(WP2PCS_ROOT_DIR,'',$root_dir); ?>" /><?php endif; ?></p>
		<p>图片访问前缀：<input type="text" name="wp_storage_to_pcs_outlink_perfix" value="<?php echo $outlink_perfix; ?>" /></p>
		<p>下载访问前缀：<input type="text" name="wp_storage_to_pcs_download_perfix" value="<?php echo $download_perfix; ?>" /></p>
		<p>附件访问方式：<select name="wp_storage_to_pcs_outlink_type">
			<option value="200" <?php selected($outlink_type,200); ?>>直链：耗流量，利于SEO</option>
			<option value="302" <?php selected($outlink_type,302); ?>>外链：省流量，SEO欠佳</option>
		</select></p>
		<p>
			<input type="submit" value="确定" class="button-primary" />
		</p>
		<input type="hidden" name="action" value="wp_storage_to_pcs_update" />
		<input type="hidden" name="page" value="<?php echo $_GET['page']; ?>" />
		<?php wp_nonce_field(); ?>
	</div>
	<div class="inside" style="border-bottom:1px solid #CCC;margin:0;padding:8px 10px;">
		<p>使用网盘中的某一个目录作为你存储图片或附件的根目录，例如你填写“/uploads/”，那么到时候就会采用这个目录下的文件作为附件。</p>
		<p>访问前缀是指用户访问你的网站的什么URL时才会调用网盘中的图片，例如你填写的是“img”，那么用户在访问“<?php echo home_url('/img/test.jpg'); ?>”时，屏幕上就会打印在你的网盘目录“/uploads/test.jpg”这张图片。为了提高不同空间的兼容性，建议你把这个前缀填写为“?img”的形式。<b>注意</b>，如果你的主机支持重写，最好填写“img”，而不是“?img”，后者将不支持中文文件。</p>
		<p>图片采取了防盗链的功能，来自网站本身、百度、谷歌以外的其他访问都会被认为是盗链行为，当然如果你懂代码，可以通过修改插件源文件来扩大图片可用范围。</p>
		<?php if($app_key === 'false') : ?>
		<p>你当前采取的是把附件托管到WP2PCS官方网盘，图片附件等将会以你的域名作为URL前缀，这种方式会消耗你的服务器流量，如果你想节约流量，可以更新授权，选择保存到自己的网盘，然后选择外链形式的图片外链访问方式。</p>
		<?php else : ?>
		<p>保护授权信息：如果你使用外链的形式，当用户鼠标右键查看原图时，（高手面前）会泄露你的access token信息，但这样别人就可以任意的使用、删除、下载你的存储空间中的文件，因此，如果你对自己的access token比较看重，可以选择直链。</p>
		<?php endif; ?>
		<p>最后，你还需要注意一些兼容性问题。这不是指插件本身的问题，而是指与其他环境的冲突，例如你使用了CDN缓存服务，就有可能造成图片缓存而不能被访问；如果你使用了其他插件来优化你的图片URL，也最好将这些插件重新设计。</p>
	</div>
	</form>
</div>
<?php
}