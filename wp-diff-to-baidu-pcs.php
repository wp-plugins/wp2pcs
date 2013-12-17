<?php

// 增加schedule,自定义的时间间隔循环的时间间隔 每周一次和每两周一次
add_filter('cron_schedules','wp2pcs_more_reccurences_for_diff');
function wp2pcs_more_reccurences_for_diff(){
	return array(
		'ten_second' => array('interval' => 10, 'display' => '10秒一次'),
		'tweenty_second' => array('interval' => 20, 'display' => '20秒一次'),
		'half_minute' => array('interval' => 30, 'display' => '30秒一次'),
		'minutely' => array('interval' => 60, 'display' => '60秒一次')
	);
}

// 更新设置
add_action('admin_init','wp_diff_to_pcs_action');
function wp_diff_to_pcs_action(){
	if(!is_admin() && !current_user_can('edit_theme_options'))return;
	if(is_multisite() && !current_user_can('manage_network')){
		return;
	}elseif(!current_user_can('edit_theme_options')){
		return;
	}
	if(!empty($_POST) && isset($_POST['page']) && $_POST['page'] == $_GET['page'] && isset($_POST['action']) && $_POST['action'] == 'wp_diff_to_pcs_send_file'){
		check_admin_referer();
		set_php_ini('timezone');
		$app_key = get_option('wp_to_pcs_app_key');
		// 更新备份到的网盘目录
		$root_dir = trim($_POST['wp_diff_to_pcs_root_dir']);
		if(
			(!$root_dir || empty($root_dir)) 
			&& (!isset($_POST['wp_diff_to_pcs_future']) || $_POST['wp_diff_to_pcs_future'] != '已经开启增量备份，现在关闭')	
		){
			wp_die('请填写备份到网盘的目录！');
			exit;
		}
		if($app_key === 'false'){ // 托管在官方
			$root_dir = WP2PCS_SUB_DIR.$root_dir;
		}else{
			$root_dir = WP2PCS_ROOT_DIR.$root_dir;
		}
		$root_dir = trailingslashit($root_dir);
		update_option('wp_diff_to_pcs_root_dir',$root_dir);
		// 要备份的目录列表
		$local_paths = trim($_POST['wp_diff_to_pcs_local_paths']);
		if(!empty($local_paths)){
			$local_paths = explode("\n",$local_paths);
			update_option('wp_diff_to_pcs_local_paths',$local_paths);
		}else{
			delete_option('wp_diff_to_pcs_local_paths');
		}
		$run_rate = isset($_POST['wp_diff_to_pcs_run_rate']) ? $_POST['wp_diff_to_pcs_run_rate'] : false;
		if($run_rate)update_option('wp_diff_to_pcs_run_rate',$run_rate);
		// 点击更新按钮，将会使所有设置立即生效
		if(isset($_POST['wp_diff_to_pcs_reset']) && $_POST['wp_diff_to_pcs_reset']=='更新'){
			delete_option('wp_diff_to_pcs_last_time');
			delete_option('wp_diff_to_pcs_local_files_cursor');
			wp_diff_to_pcs_update_file_list();
			wp_redirect(remove_query_arg('_wpnonce',add_query_arg(array('time'=>time()))).'#wp-to-pcs-diff-form');
			exit;
		}
		// 开启定时任务
		if(isset($_POST['wp_diff_to_pcs_future'])){
			update_option('wp_diff_to_pcs_future',$_POST['wp_diff_to_pcs_future']);
			if($_POST['wp_diff_to_pcs_future'] == '开启增量备份'){
				wp_schedule_event(time()+120,$run_rate,'wp_diff_to_pcs_corn_task');
			}else{
				wp_clear_scheduled_hook('wp_diff_to_pcs_corn_task');
			}
		}
		wp_redirect(remove_query_arg('_wpnonce',add_query_arg(array('time'=>time()))).'#wp-to-pcs-diff-form');
		exit;
	}
}

// 获得目录总汇，用在点击更新的时候
function wp_diff_to_pcs_update_file_list(){
	$local_paths = get_option('wp_diff_to_pcs_local_paths');
	$local_files = array();
	if(!is_array($local_paths) || empty($local_paths)){
		$local_files = array(ABSPATH);
	}
	foreach($local_paths as $path){
		if(strpos($path,ABSPATH)!==0)continue;
		if(!file_exists(trim($path)))continue;
		$get_files = array();
		if(is_file(trim($path))){
			$get_files = array($path);
		}
		elseif(is_dir($path)){
			get_files_in_dir_reset();
			$get_files = get_files_in_dir($path);
		}
		$local_files = array_merge($local_files,$get_files);
	}
	update_option('wp_diff_to_pcs_local_files',$local_files);
	return $local_files;
}

// 按照增量函数进行备份
add_action('wp_diff_to_pcs_corn_task','wp_diff_to_pcs_corn_function');
function wp_diff_to_pcs_corn_function(){
	if(get_option('wp_diff_to_pcs_future') != '开启增量备份')
		return;
	set_php_ini('limit');
	set_php_ini('timezone');

	// 获得目录总汇
	$local_files = get_option('wp_diff_to_pcs_local_files');
	if(!$local_files){
		$local_files = wp_diff_to_pcs_update_file_list();
	}

	// 对每一个文件进行检查
	$diff_cursor = get_option('wp_diff_to_pcs_local_files_cursor'); // 设置游标
	if(!$diff_cursor)$diff_cursor = 0;
	$diff_time = get_option('wp_diff_to_pcs_last_time'); // 上一次更新的时间
	if(!$diff_time)$diff_time = 0;
	// 通过对游标的判断，确认上一次同步的文件和这次应该同步第几个文件
	if(isset($local_files[$diff_cursor])){
		$local_file = $local_files[$diff_cursor];
		if(!is_file(trim($local_file)) || is_dir($local_file)){
			$diff_cursor ++;
			if(!isset($local_files[$diff_cursor])){
				$diff_cursor = 0;
				update_option('wp_diff_to_pcs_last_time',time());
			}
			update_option('wp_diff_to_pcs_local_files_cursor',$diff_cursor);
			wp_diff_to_pcs_corn_function();
			return;
		}
		$mtime = filemtime($local_file);
		if($mtime > $diff_time){
			$file_name = basename($local_file);
			$file_size = get_real_filesize($local_file);
			// 处理一些路径
			$file_path = str_replace_first(ABSPATH,'/',$local_file);
			$file_path = str_replace('\\','/',$file_path);
			$file_local_dir = dirname($file_path);
			$file_local_url = home_url($file_path);
			$remote_dir = trailingslashit(get_option('wp_diff_to_pcs_root_dir'));
			$remote_dir .= $file_local_dir;
			$remote_dir = str_replace('//','/',$remote_dir);
			$remote_dir = trailingslashit($remote_dir);
			global $baidupcs;
			// 文件大于200M时，使用离线下载功能，可以更快的传输文件，不需要在执行fopen等操作，也可以节省资源了
			if(!$file_size || $file_size > WP2PCS_BACKUP_OFFLINE_SIZE){
				$result = $baidupcs->addOfflineDownloadTask($remote_dir,$file_local_url,10*1024*1024,2*3600,'');
			}
			// 文件大于2M的时候，用分片上传
			elseif($file_size > 2*1024*1024){
				$file_blocks = array();
				$handle = @fopen($local_file,'rb');
				while(!@feof($handle)){
					$file_block_content = fread($handle,2*1024*1024);
					$temp = $baidupcs->upload($file_block_content,$remote_dir,$file_name,false,true);
					if(!is_array($temp)){
						$temp = json_decode($temp,true);
					}
					if(isset($temp['md5'])){
						array_push($file_blocks,$temp['md5']);
					}
				}
				fclose($handle);
				if(count($file_blocks) > 1){
					$result = $baidupcs->createSuperFile($remote_dir,$file_name,$file_blocks,'');
				}
			}
			// 文件小于2M的时候，直接上传
			else{
				$handle = @fopen($local_file,'rb');
				$file_content = fread($handle,$file_size);
				$result = $baidupcs->upload($file_content,$remote_dir,$file_name);
				fclose($handle);
			}
		}
		$diff_cursor ++;
		if(!isset($local_files[$diff_cursor])){
			$diff_cursor = 0;
			update_option('wp_diff_to_pcs_last_time',time());
		}
		update_option('wp_diff_to_pcs_local_files_cursor',$diff_cursor);
	}
	// 如果已经游玩了，那么就重新检查目录
	else{
		update_option('wp_diff_to_pcs_local_files_cursor',0);
		update_option('wp_diff_to_pcs_last_time',time());
		wp_diff_to_pcs_update_file_list();
		wp_diff_to_pcs_corn_function();
		return;
	}
}

// 上传新的附件的时候，将附件同步到增量备份目录
add_filter('wp_handle_upload','wp2pcs_diff_to_pcs_upload_send');
function wp2pcs_diff_to_pcs_upload_send($file){
	$local_file = $file['file'];
	$file_local_url = $file['url'];
	$file_name = basename($local_file);
	$file_size = get_real_filesize($local_file);
	// 处理一些路径
	$file_path = str_replace_first(ABSPATH,'/',$local_file);
	$file_path = str_replace('\\','/',$file_path);
	$file_local_dir = dirname($file_path);
	$remote_dir = trailingslashit(get_option('wp_diff_to_pcs_root_dir'));
	$remote_dir .= $file_local_dir;
	$remote_dir = str_replace('//','/',$remote_dir);
	$remote_dir = trailingslashit($remote_dir);
	global $baidupcs;
	// 文件大于200M时，使用离线下载功能，可以更快的传输文件，不需要在执行fopen等操作，也可以节省资源了
	if(!$file_size || $file_size > WP2PCS_BACKUP_OFFLINE_SIZE){
		$result = $baidupcs->addOfflineDownloadTask($remote_dir,$file_local_url,10*1024*1024,2*3600,'');
	}
	// 文件大于2M的时候，用分片上传
	elseif($file_size > 2*1024*1024){
		$file_blocks = array();
		$handle = @fopen($local_file,'rb');
		while(!@feof($handle)){
			$file_block_content = fread($handle,2*1024*1024);
			$temp = $baidupcs->upload($file_block_content,$remote_dir,$file_name,false,true);
			if(!is_array($temp)){
				$temp = json_decode($temp,true);
			}
			if(isset($temp['md5'])){
				array_push($file_blocks,$temp['md5']);
			}
		}
		fclose($handle);
		if(count($file_blocks) > 1){
			$result = $baidupcs->createSuperFile($remote_dir,$file_name,$file_blocks,'');
		}
	}
	// 文件小于2M的时候，直接上传
	else{
		$handle = @fopen($local_file,'rb');
		$file_content = fread($handle,$file_size);
		$result = $baidupcs->upload($file_content,$remote_dir,$file_name);
		fclose($handle);
	}
	return $file;
}

// WP2PCS菜单中，使用下面的函数，打印与备份有关的内容
function wp_diff_to_pcs_panel(){
	set_php_ini('timezone');
	$app_key = get_option('wp_to_pcs_app_key');
	$root_dir = get_option('wp_diff_to_pcs_root_dir');
	$run_rate = get_option('wp_diff_to_pcs_run_rate');
	$diff_rate = wp2pcs_more_reccurences_for_diff();
	$btn_text = (get_option('wp_diff_to_pcs_future') == '开启增量备份' ? '已经开启增量备份，现在关闭' : '开启增量备份');
	$btn_class = ($btn_text == '开启增量备份' ? 'button-primary' : 'button');
	$local_paths = get_option('wp_diff_to_pcs_local_paths');
	$local_paths = (is_array($local_paths) && !empty($local_paths) ? implode("\n",$local_paths) : '');
	$diff_timestamp = wp_next_scheduled('wp_diff_to_pcs_corn_task');
?>
<div class="postbox">
	<h3>PCS增量备份 <a href="javascript:void(0)" class="tishi-btn right">+</a></h3>
	<div class="inside" style="border-bottom:1px solid #CCC;margin:0;padding:8px 10px;">
	<form method="post" id="wp-to-pcs-diff-form">
		<?php if($diff_timestamp) : ?>
		<?php
		$local_files = get_option('wp_diff_to_pcs_local_files');
		$diff_cursor = get_option('wp_diff_to_pcs_local_files_cursor');
		$current_diff_file = $local_files[$diff_cursor];
		$next_diff_file = isset($local_files[$diff_cursor+1]) ? $local_files[$diff_cursor+1] : false;
		$totle_count = count($local_files);
		$left_count = $totle_count-$diff_cursor;
		$diff_rate_interval = $diff_rate[$run_rate]['interval'];
		$left_time_long = $diff_rate_interval * $left_count;
		$left_time_min = (int)($left_time_long/60);
		$left_time_sec = $left_time_long%60;
		$diff_rate_display = $diff_rate[$run_rate]['display'];
		echo "<p>增量备份任务：$diff_rate_display 共有 $totle_count 要备份，当前剩余 $left_count <br />";
		echo "当前备份到了：$current_diff_file <br />";
		echo ($next_diff_file ? "下一个将备份：$next_diff_file <br />" : '');
		echo "预计还需要 ".($left_time_min ? $left_time_min.' 分 ' : '').($left_time_sec ? $left_time_sec.' 秒' : '')."才能备份完你规定的路径";
		echo "请不要在这期间对网站进行大规模修改。</p>";
		?>
		<?php else : ?>
		<p>执行频率：<select name="wp_diff_to_pcs_run_rate">
			<?php foreach($diff_rate as $rate => $info) : ?>
				<option value="<?php echo $rate; ?>" <?php selected($run_rate,$rate); ?>><?php echo $info['display']; ?></option>
			<?php endforeach; ?>
		</select></p>
		<?php endif; ?>
		<p class="tishi hidden">选择合适的执行频率，对于有一些主机本身运行速度就比较慢，强烈建议选择间隔时间更长的选项。</p>
		<p>备份至网盘目录：<?php if($app_key === 'false')echo WP2PCS_SUB_DIR;else echo WP2PCS_ROOT_DIR; ?><input type="text" class="regular-text" name="wp_diff_to_pcs_root_dir"  value="<?php if($app_key === 'false')echo str_replace(WP2PCS_SUB_DIR,'',$root_dir);else echo str_replace(WP2PCS_ROOT_DIR,'',$root_dir); ?>" <?php if($diff_timestamp)echo 'readonly="readonly"'; ?> /></p>
		<p>
			只备份下列文件或目录：（和上面的定时备份一样，不填则备份整个网站）<br />
			<textarea name="wp_diff_to_pcs_local_paths" class="large-text code" style="height:90px;" <?php if($diff_timestamp)echo 'readonly="readonly"'; ?>><?php echo stripslashes($local_paths); ?></textarea>
		</p>
		<p>
			<?php if(!$diff_timestamp) : ?>
			<input type="submit" value="确定" class="button-primary" />
			<input type="submit" name="wp_diff_to_pcs_reset" value="更新" class="button-primary" onclick="if(confirm('更新将使整个备份重头再来，可能导致你当前的文件还没有备份完整，只有在特殊情况下再使用，建议你慎重考虑！') == false)return false;" />
			&nbsp;&nbsp;&nbsp;&nbsp;
			<?php endif; ?>
			<input type="submit" name="wp_diff_to_pcs_future" value="<?php echo $btn_text; ?>" class="<?php echo $btn_class; ?>" />
		</p>
		<p class="tishi hidden">为避免误操作，开启备份后需要两分钟才会开始正式执行。</p>
		<p class="tishi hidden">点击确定不会让新的备份任务立即生效，只会把新的设置记录到数据库中，在下一轮更新中才会生效。如果你希望当前的设置马上生效，点击更新后再启动备份任务，就会马上按照新设置的信息进行备份。</p>
		<input type="hidden" name="action" value="wp_diff_to_pcs_send_file" />
		<input type="hidden" name="page" value="<?php echo $_GET['page']; ?>" />
		<?php wp_nonce_field(); ?>
	</form>
	</div>
	<div class="inside tishi hidden" style="border-bottom:1px solid #CCC;margin:0;padding:8px 10px;">
		<p>什么是增量备份：严格意义上讲，wp2pcs提供的该功能为类似增量备份功能，即通过对文件检查，只上传经过修改的文件，已经备份过的，但没有发生变化的文件不进行备份，从而节省了大量资源。</p>
		<p>wp2pcs提供的增量备份功能性能上受限于当前主机，如果你发现备份似乎不尽人意，建议开启简易加速，以尽快备份完你的文件。开启简易加速后，每次进入后台都会触发一次备份请求，URL会多出一个参数，前台不受影响。</p>
	</div>
</div>
<?php
}