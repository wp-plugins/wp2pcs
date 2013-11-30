<?php

/*
*
* # 这个文件是用来实现从百度网盘获取附件列表，并让站长可以选择插入到文章中
* # http://wordpress.stackexchange.com/questions/85351/remove-other-tabs-in-new-wordpress-media-gallery
*

http://sumtips.com/2012/12/add-remove-tab-wordpress-3-5-media-upload-page.html
https://gist.github.com/Fab1en/4586865
http://wordpress.stackexchange.com/questions/76980/add-a-menu-item-to-wordpress-3-5-media-manager
http://cantina.co/2012/05/15/tutorial-writing-a-wordpress-plugin-using-the-media-upload-tab-2/
http://wordpress.stackexchange.com/questions/76980/add-a-menu-item-to-wordpress-3-5-media-manager
http://stackoverflow.com/questions/5671550/jquery-window-send-to-editor
http://wordpress.stackexchange.com/questions/50873/how-to-handle-multiple-instance-of-send-to-editor-js-function
http://codeblow.com/questions/jquery-window-send-to-editor/
http://wordpress.stackexchange.com/questions/85351/remove-other-tabs-in-new-wordpress-media-gallery
*/

// 在新媒体管理界面添加一个百度网盘的选项
add_filter('media_upload_tabs', 'wp_storage_to_pcs_media_tab' );
function wp_storage_to_pcs_media_tab($tabs){
	if(!is_wp_to_pcs_active())return;
	$newtab = array('file_from_pcs' => '百度网盘');
    return array_merge($tabs,$newtab);
}
// 这个地方需要增加一个中间介wp_iframe，这样就可以使用wordpress的脚本和样式
add_action('media_upload_file_from_pcs', 'media_upload_file_from_pcs_iframe');
function media_upload_file_from_pcs_iframe() {
	wp_iframe('wp_storage_to_pcs_media_tab_box');
}
// 在上面产生的百度网盘选项中要显示出网盘内的文件
//add_action('media_upload_file_from_pcs','wp_storage_to_pcs_media_tab_box');
function wp_storage_to_pcs_media_tab_box() {
	// 当前路径相关信息
	$root_dir = get_option('wp_storage_to_pcs_root_dir');	
	$access_token = WP2PCS_APP_TOKEN;
	if(isset($_GET['dir']) && !empty($_GET['dir'])){
		$dir_pcs_path = $_GET['dir'];
	}else{
		$dir_pcs_path = $root_dir;
	}
	if(isset($_GET['paged']) && is_numeric($_GET['paged']) && $_GET['paged'] > 1){
		$paged = $_GET['paged'];
	}else{
		$paged = 1;
	}
	$app_key = get_option('wp_to_pcs_app_key');
?>
<style>
#opt-on-pcs-tabs{padding:2em 1em 1em 1em;border-bottom:1px solid #dedede;margin-bottom:1em;font-size:1.1em;}
#files-on-pcs{margin:10px;}
.file-on-pcs{width:120px;height:120px;overflow:hidden;float:left;margin:5px;padding:2px;}
.file-thumbnail{width:120px;height:96px;overflow:hidden;background-color:#f1f1f1;}
.file-type-dir .file-thumbnail{background-color:#FDCE5F;}
.file-thumbnail img{max-width:100%;height:auto;}
.file-name{line-height:1em;margin-top:3px;}
.selected{background-color:#008000;color:#fff;}
.selected-file{background-color:#A30000;}
.opt-area{margin:0 10px;}
.alert{color:#D44B25;margin:0 10px;}
.hidden{display:none;}
#upload-to-pcs{text-align:center;padding:5em 0;}
</style>
<script>
jQuery(function($){
	$('#files-on-pcs div.can-select').click(function(){
		$(this).toggleClass('selected');
		if($(this).attr('data-file-type') == 'file'){
			$(this).toggleClass('selected-file');
		}
	});
	$('#insert-btn').click(function(){
		if($('div.selected').length > 0){
			var $outlink_perfix = '<?php echo trim(get_option('wp_storage_to_pcs_outlink_perfix')); ?>',
				$download_perfix = '<?php echo trim(get_option('wp_storage_to_pcs_download_perfix')); ?>',
				$root_dir = '<?php echo trim(get_option('wp_storage_to_pcs_root_dir')); ?>',
				$home_url = '<?php echo home_url('/'); ?>',
				$img_root = $home_url + $outlink_perfix + '/',
				$download_root = $home_url + $download_perfix + '/',
				$html = '';
			$('div.selected').each(function(){
				var $this = $(this),
					$file_name = $this.attr('data-file-name'),
					$file_path = $this.attr('data-file-path'),
					$file_type = $this.attr('data-file-type'),
					$img_src = $img_root + $file_path.replace($root_dir,''),
					$file_src = $download_root + $file_path.replace($root_dir,'');
				if($file_type == 'image')$html += '<img src="' + $img_src + '" />';
				else $html += '<a href="' + $file_src + '">' + $file_name + '</a>';
			});
			$('div.selected').removeClass('selected');
			// http://stackoverflow.com/questions/13680660/insert-content-to-wordpress-post-editor
			window.parent.send_to_editor($html);
			window.parent.tb_remove();
		}else{
			alert('没有选择任何附件');
		}
	});
	$('#close-btn').click(function(){
		window.parent.tb_remove();
	});
	$('#upload-to-pcs-submit').click(function(){
		var $upload_path = '<?php echo $dir_pcs_path; ?>/',
			$file_name = $('#upload-to-pcs-input').val().match(/[^\/|\\]*$/)[0],
			$action = 'http://wp2pcs.duapp.com/upload?<?php echo get_option("wp_to_pcs_site_id"); ?>+<?php echo substr(get_option("wp_to_pcs_access_token"),0,10); ?>+path=' + $upload_path + $file_name;
		<?php if(strpos(get_option('wp_storage_to_pcs_outlink_perfix'),'?') !== false) : ?>
		if(/.*[\u4e00-\u9fa5]+.*$/.test($file_name)){
			alert('不支持含有汉字的图片名');
			return false;
		}
		<?php endif; ?>
		if($file_name != ''){
			$('#upload-to-pcs-refresh').addClass('hidden');
			$('#upload-to-pcs-from').attr('action',$action).submit();
			$('#upload-to-pcs-processing').removeClass('hidden');
			$is_uploading = setInterval(function(){
				$('#upload-to-pcs-window').load(function(){
					$('#upload-to-pcs-refresh').removeClass('hidden');
					$('#upload-to-pcs-processing').addClass('hidden');
					clearInterval($is_uploading);
				});				
			},500);
		}
	});
	$('#show-upload-area').toggle(function(e){
		e.preventDefault();
		$('#files-on-pcs').hide();
		$('#upload-to-pcs').show();
		$(this).text('返回列表');
	},function(e){
		e.preventDefault();
		$('#files-on-pcs').show();
		$('#upload-to-pcs').hide();
		$(this).text('上传到这里');
	});
});
</script>
<div id="opt-on-pcs-tabs">
	当前位置：<a href="<?php echo remove_query_arg('dir'); ?>">HOME</a><?php
	if(isset($_GET['dir']) && !empty($_GET['dir'])){
		$current_path = str_replace($root_dir,'',$dir_pcs_path);
		$current_dir_string = array();
		$current_path_arr = array_filter(explode('/',$current_path));
		if(!empty($current_path_arr))foreach($current_path_arr as $key => $current_dir){
			$current_dir_string[] = $current_dir;
			$current_dir_link = implode('/',$current_dir_string);
			$current_dir_link = add_query_arg('dir',$root_dir.$current_dir_link);
			$current_dir_link = '/<a href="'.$current_dir_link.'">'.$current_dir.'</a>';
			echo $current_dir_link;
		}
	}
	?> <a href="#upload-to-pcs" class="button" id="show-upload-area">上传到这里</a>
</div>
<div id="files-on-pcs">
<?php
	$files_per_page = 7*5;// 每行7个，行数可以自己修改
	$limit = (($paged-1)*$files_per_page).'-'.($paged*$files_per_page-1);
	$files_on_pcs = wp_storage_to_pcs_media_list_files($dir_pcs_path,$limit);
	$files_count = count($files_on_pcs);
	//print_r($files_on_pcs);
	if(!empty($files_on_pcs))foreach($files_on_pcs as $file){
		$file_name = explode('/',$file->path);
		$file_name = $file_name[count($file_name)-1];
		$file_ext = substr($file_name,strpos($file_name,'.')+1);
		$file_type = $file_ext;
		$link = false;
		$thumbnail = false;
		// 判断是否为图片
		if(in_array($file_type,array('jpg','jpeg','png','gif','bmp'))){
			$thumbnail = wp_storage_to_pcs_media_thumbnail($file->path);
			$file_type = 'image';
		}else{
			$file_type = 'file';
		}
		// 判断是否为文件（图片）还是文件夹
		if($file->isdir === 0){
			$class = ' file-type-file can-select ';
		}else{
			$class = ' file-type-dir ';
			$link = true;
			$file_type = 'dir';
		}
		// 判断路径中是否包含中文，如果前缀形式中带?，而路径中包含中文，就无法访问到，因此，要去除这种情况
		if(strpos(get_option('wp_storage_to_pcs_outlink_perfix'),'?') !== false && preg_match('/[一-龥]/u',$file_name)){
			$link = false;
			if($file_type == 'image')$file_type = 'file';
		}
		echo '<div class="file-on-pcs'.$class.'" data-file-name="'.$file_name.'" data-file-type="'.$file_type.'" data-file-path="'.$file->path.'">';
		if($link)echo '<a href="'.add_query_arg('dir',$file->path).'">';
		echo '<div class="file-thumbnail">';
		if($thumbnail)echo '<img src="'.$thumbnail.'" />';
		echo '</div>';
		echo '<div class="file-name">';
		echo $file_name;
		echo '</div>';
		if($link)echo '</a>';
		echo '</div>';
	}
	echo '<div style="clear:both;"></div>';
?>
</div>
<div id="upload-to-pcs" style="display:none;">
	<form name="input" action="#" method="post" target="upload-to-pcs-window" enctype="multipart/form-data" id="upload-to-pcs-from">
		<input type="file" name="select" id="upload-to-pcs-input" />
		<input type="button" value="上传" class="button-primary" id="upload-to-pcs-submit" />
		<a href="" class="button hidden" id="upload-to-pcs-refresh">成功，刷新查看</a>
		<img src="<?php echo plugins_url( 'asset/loading.gif',WP2PCS_PLUGIN_NAME); ?>" class="hidden" id="upload-to-pcs-processing" />
	</form>
	<iframe name="upload-to-pcs-window" id="upload-to-pcs-window" style="display:none;"></iframe>
</div>
<div class="opt-area">
	<p>
		<button id="insert-btn" class="button-primary">插入</button>
		<button id="close-btn" class="button">关闭</button>
		<?php if($paged > 1){
			echo '<a href="'.remove_query_arg('paged').'">第一页</a> 
			<a href="'.add_query_arg('paged',$paged-1).'">上一页</a>';
		}?>
		<?php if($files_count >= $files_per_page)echo '<a href="'.add_query_arg('paged',$paged+1).'">下一页</a>'; ?>
		<?php if($app_key != 'false') : ?><a href="http://pan.baidu.com/disk/home#dir/path=<?php echo $dir_pcs_path; ?>" target="_blank" class="button">管理</a><?php endif; ?>
		<a href="" class="button">刷新</a>
		<a href="<?php echo remove_query_arg('dir'); ?>" class="button">返回HOME</a>
	</p>
</div>
<div class="alert">
	<?php if(strpos(get_option('wp_storage_to_pcs_outlink_perfix'),'?') !== false) : ?><p>注意：中文字符串在百度网盘的API调用中无法使用，因此极其强烈要求你不要使用中文名的文件（夹），否则你可能不能得到想要的外链结果。为了防止错误，本插件规定：中文名的文件夹没有任何作用，中文名的图片插入时以下载链接的形式插入。</p><?php endif; ?>
	<p>如何使用：点击列表中的文件以选择它们，点击插入按钮就可以将选中的文件插入。点击之后背景变绿的，会插入图片，变红的，会插入链接。点击上传按钮会进入你的网盘目录，你上传完文件之后，再点击刷新按钮就可以看到上传完成后的图片。当你进入多个子目录之后，点击返回按钮返回网盘存储根目录。</p>
	<p>本插件本地上传功能比较弱，会极大的消耗服务器资源。请在网盘中上传（客户端或网页端都可以），完成之后请点击刷新按钮以查看新上传的文件。</p>
</div>
<?php
}
// 用一个函数来列出PCS中某个目录下的所有文件（夹）
function wp_storage_to_pcs_media_list_files($dir_pcs_path,$limit){
	$access_token = WP2PCS_APP_TOKEN;
	$order_by = 'time';
	$order = 'desc';
	$pcs = new BaiduPCS($access_token);
	$results = $pcs->listFiles($dir_pcs_path,$order_by,$order,$limit);
	$results = json_decode($results);
	$results = $results->list;
	return $results;
}
// 用一个函数来显示这些文件（或目录）
function wp_storage_to_pcs_media_thumbnail($file_pcs_path,$width = 120,$height = 1600,$quality = 100){
	$app_key = get_option('wp_to_pcs_app_key');
	$access_token = WP2PCS_APP_TOKEN;
	// 使用直链，有利于快速显示图片
	$image_outlink_per = trim(get_option('wp_storage_to_pcs_outlink_perfix'));
	$file_pcs_path = str_replace(trailingslashit(get_option('wp_storage_to_pcs_root_dir')),'/',$file_pcs_path);
	$thumbnail = home_url('/'.$image_outlink_per.$file_pcs_path);
	// 原本想使用外链，以节省流量
	/**
	$thumbnail = 'https://pcs.baidu.com/rest/2.0/pcs/thumbnail?method=generate&access_token='.$access_token.'&path='.$file_pcs_path.'&quality='.$quality.'&width='.$width.'&height='.$height;
	}
	**/
	return $thumbnail;
}