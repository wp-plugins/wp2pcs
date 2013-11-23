<?php

/*
*
* # 定时任务使用到wp-corn，所以需要一些准备
* http://dhblog.org/28.html
* http://www.neoease.com/wordpress-cron/
*
*/

// 增加schedule,自定义的时间间隔循环的时间间隔 每周一次和每两周一次
add_filter('cron_schedules','wp2pcs_more_reccurences');
function wp2pcs_more_reccurences(){
	return array(
		'daily' => array('interval' => 3600*24, 'display' => 'Once a day'),
		'doubly' => array('interval' => 3600*24*2, 'display' => 'Once two days'),
		'weekly' => array('interval' => 3600*24*7, 'display' => 'Once a week'),
		'biweekly' => array('interval' => 3600*24*7*2, 'display' => 'Once two weeks'),
		'monthly' => array('interval' => 3600*24*30, 'display' => 'Once a month'),
	);
}

// 添加处理
add_action('init','wp_backup_to_pcs_action');
function wp_backup_to_pcs_action(){
	if(!is_admin() && !current_user_can('edit_theme_options'))return;
	if(is_multisite() && !current_user_can('manage_network')){
		return;
	}elseif(!current_user_can('edit_theme_options')){
		return;
	}
	// 备份到百度网盘
	if(!empty($_POST) && isset($_POST['page']) && $_POST['page'] == $_GET['page'] && isset($_POST['action']) && $_POST['action'] == 'wp_backup_to_pcs_send_file'){
		check_admin_referer();
		date_default_timezone_set("PRC");// 把时间控制在中国
		$app_key = get_option('wp_to_pcs_app_key');
		// 更新备份到的网盘目录
		$root_dir = trim($_POST['wp_backup_to_pcs_root_dir']);
		if(
			(!$root_dir || empty($root_dir)) 
			&& (!isset($_POST['wp_backup_to_pcs_future']) || $_POST['wp_backup_to_pcs_future'] != '已经开启定时备份，现在关闭')
		){
			wp_die('请填写备份到网盘的目录！');
			exit;
		}
		if($app_key == 'false'){ // 托管在官方
			$root_dir = WP2PCS_SUB_DIR.$root_dir;
		}else{
			$root_dir = WP2PCS_ROOT_DIR.$root_dir;
		}
		$root_dir = trailingslashit($root_dir);
		update_option('wp_backup_to_pcs_root_dir',$root_dir);
		// 更新网站的日志目录
		if(trim($_POST['wp_backup_to_pcs_log_dir']) != ''){
			$log_dir = trailingslashit($_POST['wp_backup_to_pcs_log_dir']);
			update_option('wp_backup_to_pcs_log_dir',$log_dir);
		}else{
			delete_option('wp_backup_to_pcs_log_dir');
		}
		// 更新定时日周期
		$run_date = isset($_POST['wp_backup_to_pcs_run_date']) ? $_POST['wp_backup_to_pcs_run_date'] : false;
		if($run_date)update_option('wp_backup_to_pcs_run_date',$run_date);
		// 更新定时时间点
		$run_time = isset($_POST['wp_backup_to_pcs_run_time']) ? $_POST['wp_backup_to_pcs_run_time'] : false;
		if($run_time)update_option('wp_backup_to_pcs_run_time',$run_time);
		// 要备份的目录列表
		$local_paths = trim($_POST['wp_backup_to_pcs_local_paths']);
		if(!empty($local_paths)){
			$local_paths = explode("\n",$local_paths);
			update_option('wp_backup_to_pcs_local_paths',$local_paths);
		}else{
			delete_option('wp_backup_to_pcs_local_paths');
		}
		// 压缩下载
		if(isset($_POST['wp_backup_to_pcs_zip']) && $_POST['wp_backup_to_pcs_zip'] == '压缩下载' && IS_WP2PCS_WRITABLE){
			$zip_dir = trailingslashit(WP_CONTENT_DIR);
			// 备份数据库
			$database_file = $zip_dir.'database.sql';
			if(file_exists($database_file))unlink($database_file);
			$database_content = get_database_backup_all_sql();
			$handle = fopen($database_file,"w+");
			if(fwrite($handle,$database_content) === false){
				echo "写入文件 $filename 失败";
				exit();
			}
			fclose($handle);
			// 备份日志
			if($log_dir){
				$log_file = zip_files_in_dirs($log_dir,$zip_dir.'logs.zip',$log_dir);
			}
			// 备份网站
			if($local_paths && !empty($local_paths)){
				$www_file = zip_files_in_dirs($local_paths,$zip_dir.'www.zip',ABSPATH);
			}
			if($log_file || $www_file){
				if($log_file && $www_file){
					$zip_file = zip_files_in_dirs(array($database_file,$log_file,$www_file),$zip_dir.'wp2pcs.zip',$zip_dir);
				}elseif($log_file){
					$zip_file = zip_files_in_dirs(array($database_file,$log_file),$zip_dir.'wp2pcs.zip',$zip_dir);
				}elseif($www_file){
					$zip_file = zip_files_in_dirs(array($database_file,$www_file),$zip_dir.'wp2pcs.zip',$zip_dir);
				}else{
					wp_die('没有需要打包的文件！');
					exit;
				}
				header("Content-type: application/octet-stream");
				header("Content-disposition: attachment; filename=".basename($zip_file));
				$file_content = '';
				$handle = fopen($zip_file,'rb');
				while(!feof($handle)){
					$file_content .= fread($handle,2*1024*1024);
				}
				fclose($handle);
				echo $file_content;
				unlink($zip_file);
				if(file_exists($log_file))unlink($log_file);
				if(file_exists($www_file))unlink($www_file);
				unlink($database_file);
				exit;
			}
		}
		// 立即备份
		if(isset($_POST['wp_backup_to_pcs_now']) && $_POST['wp_backup_to_pcs_now'] == '马上备份'){
			set_time_limit(0); // 延长执行时间，防止备份失败
			ini_set('memory_limit','200M'); // 扩大内存限制，防止备份溢出
			$zip_dir = trailingslashit(WP_CONTENT_DIR);
			$remote_dir = $root_dir.date('Y.m.d_H.i.s').'/';
			$access_token = get_option('wp_to_pcs_access_token');
			$pcs = new BaiduPCS($access_token);
			
			// 备份数据库
			$file_content = get_database_backup_all_sql();
			$file_name = 'database.sql';
			$pcs->upload($file_content,$remote_dir,$file_name,'');
			
			// 备份日志
			if($log_dir && IS_WP2PCS_WRITABLE){
				$log_file = zip_files_in_dirs($log_dir,$zip_dir.'logs.zip',$log_dir);
				if($log_file){
					wp_backup_to_pcs_send_file($log_file,$remote_dir);
				}
			}
			
			// 备份网站内的所有文件
			if($local_paths && !empty($local_paths) && IS_WP2PCS_WRITABLE){
				$www_file = zip_files_in_dirs($local_paths,$zip_dir.'www.zip',ABSPATH);
				if($www_file){
					wp_backup_to_pcs_send_file($www_file,$remote_dir);
				}
			}
		}
		// 定时备份，需要和下面的wp_backup_to_pcs_corn_task_function函数结合起来
		if(isset($_POST['wp_backup_to_pcs_future'])){
			update_option('wp_backup_to_pcs_future',$_POST['wp_backup_to_pcs_future']);
			if($_POST['wp_backup_to_pcs_future'] == '开启定时'){
				// 开启定时任务
				if(date('Y-m-d '.$run_time.':00') < date('Y-m-d H:i:s')){
					$run_time = date('Y-m-d '.$run_time.':00',strtotime('+1 day'));				
				}else{
					$run_time = date('Y-m-d '.$run_time.':00');
				}
				$run_time = strtotime($run_time);
				foreach($run_date as $task => $date){
					if($date != 'never')wp_schedule_event($run_time,$date,'wp_backup_to_pcs_corn_task_'.$task);
				}
			}else{
				// 关闭定时任务
				if(wp_next_scheduled('wp_backup_to_pcs_corn_task_database'))
					wp_clear_scheduled_hook('wp_backup_to_pcs_corn_task_database');
				if(wp_next_scheduled('wp_backup_to_pcs_corn_task_logs'))
					wp_clear_scheduled_hook('wp_backup_to_pcs_corn_task_logs');
				if(wp_next_scheduled('wp_backup_to_pcs_corn_task_www'))
					wp_clear_scheduled_hook('wp_backup_to_pcs_corn_task_www');
			}
		}
		wp_redirect(remove_query_arg('_wpnonce',add_query_arg(array('time'=>time()))));
		exit;
	}
}

// 函数wp_backup_to_pcs_corn_task_function按照规定的时间执行备份动作
add_action('wp_backup_to_pcs_corn_task_database','wp_backup_to_pcs_corn_task_function_database');
add_action('wp_backup_to_pcs_corn_task_logs','wp_backup_to_pcs_corn_task_function_logs');
add_action('wp_backup_to_pcs_corn_task_www','wp_backup_to_pcs_corn_task_function_www');
function wp_backup_to_pcs_corn_task_function_database() {
	if(trim(get_option('wp_backup_to_pcs_future')) != '开启定时')
		return;
	$run_date = get_option('wp_backup_to_pcs_run_date');
	if(!isset($run_date['database']) || $run_date['database'] == 'never')
		return;
	set_time_limit(0); // 延长执行时间，防止备份失败
	ini_set('memory_limit','200M'); // 扩大内存限制，防止备份溢出
	date_default_timezone_set("PRC");// 使用东八区时间，如果你是其他地区的时间，自己修改
	$access_token = get_option('wp_to_pcs_access_token');
	$remote_dir = trailingslashit(get_option('wp_backup_to_pcs_root_dir')).date('Y.m.d_H.i.s').'/';
	$pcs = new BaiduPCS($access_token);
	
	// 备份数据库
	$file_content = get_database_backup_all_sql();
	$file_name = 'database.sql';
	$result = $pcs->upload($file_content,$remote_dir,$file_name,'');
}
function wp_backup_to_pcs_corn_task_function_logs(){
	if(!IS_WP2PCS_WRITABLE){
		return;
	}
	if(get_option('wp_backup_to_pcs_future') != '开启定时')
		return;
	$log_dir = get_option('wp_backup_to_pcs_log_dir');
	if(!$log_dir)
		return;
	$run_date = get_option('wp_backup_to_pcs_run_date');
	if(!isset($run_date['logs']) || $run_date['logs'] == 'never')
		return;
	set_time_limit(0); // 延长执行时间，防止备份失败
	ini_set('memory_limit','200M'); // 扩大内存限制，防止备份溢出
	date_default_timezone_set("PRC");// 使用东八区时间，如果你是其他地区的时间，自己修改
	$access_token = get_option('wp_to_pcs_access_token');
	$zip_dir = trailingslashit(WP_CONTENT_DIR);
	$remote_dir = trailingslashit(get_option('wp_backup_to_pcs_root_dir')).date('Y.m.d_H.i.s').'/';
	$pcs = new BaiduPCS($access_token);

	// 备份日志
	$log_file = zip_files_in_dirs($log_dir,$zip_dir.'logs.zip',$log_dir);
	if($log_file){
		wp_backup_to_pcs_send_file($log_file,$remote_dir);
	}
}
function wp_backup_to_pcs_corn_task_function_www(){
	if(!IS_WP2PCS_WRITABLE){
		return;
	}
	if(trim(get_option('wp_backup_to_pcs_future')) != '开启定时')
		return;
	$local_paths = get_option('wp_backup_to_pcs_local_paths');
	if(!$local_paths || empty($local_paths))
		return;
	$run_date = get_option('wp_backup_to_pcs_run_date');
	if(!isset($run_date['www']) || $run_date['www'] == 'never')
		return;
	set_time_limit(0); // 延长执行时间，防止备份失败
	ini_set('memory_limit','200M'); // 扩大内存限制，防止备份溢出
	date_default_timezone_set("PRC");// 使用东八区时间，如果你是其他地区的时间，自己修改
	$access_token = get_option('wp_to_pcs_access_token');
	$zip_dir = trailingslashit(WP_CONTENT_DIR);
	$remote_dir = trailingslashit(get_option('wp_backup_to_pcs_root_dir')).date('Y.m.d_H.i.s').'/';
	$pcs = new BaiduPCS($access_token);

	// 备份网站内的所有文件
	$www_file = zip_files_in_dirs($local_paths,$zip_dir.'www.zip',ABSPATH);
	if($www_file){
		wp_backup_to_pcs_send_file($www_file,$remote_dir);
	}
}

// 创建一个函数直接将单个文件送到百度盘
function wp_backup_to_pcs_send_single_file($local_path,$remote_dir){
	$access_token = get_option('wp_to_pcs_access_token');
	$pcs = new BaiduPCS($access_token);
	$file_name = basename($local_path);
	$file_size = filesize($local_path);
	$handle = fopen($local_path,'rb');
	$file_content = fread($handle,$file_size);
	$pcs->upload($file_content,trailingslashit($remote_dir),$file_name,'');
	fclose($handle);
	unlink($local_path);
}

// 超大文件分片上传函数
function wp_backup_to_pcs_send_super_file($local_path,$remote_dir,$file_block_size){
	$access_token = get_option('wp_to_pcs_access_token');
	$pcs = new BaiduPCS($access_token);
	$file_blocks = array();//分片上传文件成功后返回的md5值数组集合
	$file_name = basename($local_path);
	$handle = fopen($local_path,'rb');
	while(!feof($handle)){
		$file_block_content = fread($handle,$file_block_size);
		$temp = $pcs->upload($file_block_content,trailingslashit($remote_dir),$file_name,'',true);
		if(!is_array($temp)){
			$temp = json_decode($temp,true);
		}
		if(isset($temp['md5'])){
			array_push($file_blocks,$temp['md5']);
		}
	}
	fclose($handle);
	unlink($local_path);
	if(count($file_blocks) > 1){
		$pcs->createSuperFile(trailingslashit($remote_dir),$file_name,$file_blocks,'');
	}
}

// 创建一个函数来确定采取什么上传方式，并执行这种方式的上传
function wp_backup_to_pcs_send_file($local_path,$remote_dir){
	$file_size = filesize($local_path);
	$file_max_size = 2*1024*1024;
	if($file_size > $file_max_size){
		wp_backup_to_pcs_send_super_file($local_path,$remote_dir,$file_max_size);
	}else{
		wp_backup_to_pcs_send_single_file($local_path,$remote_dir);
	}
}

// WP2PCS菜单中，使用下面的函数，打印与备份有关的内容
function wp_backup_to_pcs_panel(){
	date_default_timezone_set("PRC");
	$app_key = get_option('wp_to_pcs_app_key');
	$access_token = get_option('wp_to_pcs_access_token');
	$root_dir = get_option('wp_backup_to_pcs_root_dir');
	$run_date_arr = get_option('wp_backup_to_pcs_run_date');
	$run_time = get_option('wp_backup_to_pcs_run_time');
	$log_dir = get_option('wp_backup_to_pcs_log_dir');
	$btn_text = (get_option('wp_backup_to_pcs_future') == '开启定时' ? '已经开启定时备份，现在关闭' : '开启定时');
	$btn_class = ($btn_text == '开启定时' ? 'button-primary' : 'button');
	$timestamp_database = wp_next_scheduled('wp_backup_to_pcs_corn_task_database');
	$timestamp_database = ($timestamp_database ? date('Y-m-d H:i',$timestamp_database) : false);
	$timestamp_logs = wp_next_scheduled('wp_backup_to_pcs_corn_task_logs');
	$timestamp_logs = ($timestamp_logs ? date('Y-m-d H:i',$timestamp_logs) : false);
	$timestamp_www = wp_next_scheduled('wp_backup_to_pcs_corn_task_www');
	$timestamp_www = ($timestamp_www ? date('Y-m-d H:i',$timestamp_www) : false);
	$local_paths = get_option('wp_backup_to_pcs_local_paths');
	$local_paths = (is_array($local_paths) && !empty($local_paths) ? implode("\n",$local_paths) : '');
?>
<div class="postbox">
	<h3>PCS备份设置</h3>
	<form method="post" id="wp-to-pcs-backup-form">
	<div class="inside" style="border-bottom:1px solid #CCC;margin:0;padding:8px 10px;">
		<?php if($timestamp_database || $timestamp_logs || $timestamp_www): ?>
		<p><b>下一次自动备份时间</b>：
			<?php echo ($timestamp_database ? '数据库：'.$timestamp_database : ''); ?>
			<?php echo ($timestamp_logs ? '日志：'.$timestamp_logs : ''); ?>
			<?php echo ($timestamp_www ? '网站：'.$timestamp_www : ''); ?>
			<br />
			要重新规定备份时间，必须先关闭定时备份。
		</p>
		<?php else : ?>
		<p>定时备份：
			数据库<select name="wp_backup_to_pcs_run_date[database]"><?php $run_date = $run_date_arr['database']; ?>
				<option <?php selected($run_date,'daily'); ?> value="daily">每天</option>
				<option <?php selected($run_date,'doubly'); ?> value="doubly">两天</option>
				<option <?php selected($run_date,'weekly'); ?> value="weekly">每周</option>
				<option <?php selected($run_date,'biweekly'); ?> value="biweekly">两周</option>
				<option <?php selected($run_date,'monthly'); ?> value="monthly">每月</option>
				<option <?php selected($run_date,'never'); ?> value="never">永不</option>
			</select> 
			<?php if(IS_WP2PCS_WRITABLE) : ?>
			日志<select name="wp_backup_to_pcs_run_date[logs]"><?php $run_date = $run_date_arr['logs']; ?>
				<option <?php selected($run_date,'daily'); ?> value="daily">每天</option>
				<option <?php selected($run_date,'doubly'); ?> value="doubly">两天</option>
				<option <?php selected($run_date,'weekly'); ?> value="weekly">每周</option>
				<option <?php selected($run_date,'biweekly'); ?> value="biweekly">两周</option>
				<option <?php selected($run_date,'monthly'); ?> value="monthly">每月</option>
				<option <?php selected($run_date,'never'); ?> value="never">永不</option>
			</select> 
			网站<select name="wp_backup_to_pcs_run_date[www]"><?php $run_date = $run_date_arr['www']; ?>
				<option <?php selected($run_date,'daily'); ?> value="daily">每天</option>
				<option <?php selected($run_date,'doubly'); ?> value="doubly">两天</option>
				<option <?php selected($run_date,'weekly'); ?> value="weekly">每周</option>
				<option <?php selected($run_date,'biweekly'); ?> value="biweekly">两周</option>
				<option <?php selected($run_date,'monthly'); ?> value="monthly">每月</option>
				<option <?php selected($run_date,'never'); ?> value="never">永不</option>
			</select> 
			<?php endif; ?>
			时间：<select name="wp_backup_to_pcs_run_time">
				<option <?php selected($run_time,'00:00'); ?>>00:00</option>
				<option <?php selected($run_time,'01:00'); ?>>01:00</option>
				<option <?php selected($run_time,'02:00'); ?>>02:00</option>
				<option <?php selected($run_time,'03:00'); ?>>03:00</option>
				<option <?php selected($run_time,'04:00'); ?>>04:00</option>
				<option <?php selected($run_time,'05:00'); ?>>05:00</option>
				<option <?php selected($run_time,'06:00'); ?>>06:00</option>
			</select>
		</p>
		<?php endif; ?>
		<p>备份至网盘目录：<?php if($app_key == 'false') : echo WP2PCS_SUB_DIR; ?><input type="text"  class="regular-text" name="wp_backup_to_pcs_root_dir"  value="<?php echo str_replace(WP2PCS_SUB_DIR,'',$root_dir); ?>" /><?php else : echo WP2PCS_ROOT_DIR; ?><input type="text" name="wp_backup_to_pcs_root_dir" class="regular-text" value="<?php echo str_replace(WP2PCS_ROOT_DIR,'',$root_dir); ?>" /><?php endif; ?></p>
		<?php if(IS_WP2PCS_WRITABLE) : ?>
		<p>当前网站的日志文件夹路径：<input type="text" name="wp_backup_to_pcs_log_dir" class="regular-text" value="<?php echo $log_dir; ?>" /></p>
		<p>
			只备份下列文件或目录：（务必阅读下方说明，根路径为：<?php echo ABSPATH; ?>）<br />
			<textarea name="wp_backup_to_pcs_local_paths" class="large-text code" style="height:90px;"><?php echo stripslashes($local_paths); ?></textarea>
		</p>
		<?php endif; ?>
		<p>
			<input type="submit" value="确定" class="button-primary" />
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="submit" name="wp_backup_to_pcs_future" value="<?php echo $btn_text; ?>" class="<?php echo $btn_class; ?>" />
			<?php if(IS_WP2PCS_WRITABLE) : ?>
			<input type="submit" name="wp_backup_to_pcs_now" value="马上备份" class="button-primary" onclick="if(confirm('境外主机由于和百度服务器通信可能存在障碍，可能备份不成功，你可以使用“压缩下载”功能，先下载备份包，然后上传到网盘中！！') == false)return false;if(confirm('马上备份会备份整站或所填写的目录或文件列表，而且现在备份会花费大量的服务器资源，建议在深夜的时候进行！点击“确定”现在备份，点击“取消”则不备份') == false)return false;" />
			<input type="submit" name="wp_backup_to_pcs_zip" value="压缩下载" class="button-primary" onclick="if(confirm('压缩下载会花费大量的服务器资源，建议在深夜的时候进行！点击“确定”现在下载，点击“取消”则不备份') == false){return false;}else{jQuery('#wp-to-pcs-backup-form').attr('target','_blank');setTimeout(function(){jQuery('#wp-to-pcs-backup-form').attr('target','_self');},500);}" />
			<?php if(!class_exists('ZipArchive')){echo '<b>当前服务器不支持插件打包方式，只有数据库可以被备份。</b>';} ?>
			<?php endif; ?>
		</p>
		<input type="hidden" name="action" value="wp_backup_to_pcs_send_file" />
		<input type="hidden" name="page" value="<?php echo $_GET['page']; ?>" />
		<?php wp_nonce_field(); ?>
	</div>
	<?php 
	if($app_key == 'false') : // 当使用托管服务时，允许用户下载和删除
		$pcs = new BaiduPCS($access_token);
		$results = $pcs->listFiles($root_dir,'time','desc','0-10');
		$results = json_decode($results);
		$results = $results->list;
		if(!empty($results)){
			echo '<div class="inside" style="border-bottom:1px solid #CCC;margin:0;padding:8px 10px;">';
			?>
			<iframe name="wp_backup_to_pcs_opt_packs_iframe" id="wp_backup_to_pcs_opt_packs_iframe" style="display:none;" id=""></iframe>
			<script>
			function wp_backup_to_pcs_delete(object){
				if(!confirm('删除操作将不能取消，是否继续？'))return false;
				jQuery(object).parent().fadeOut();
			}
			</script>
			<?php
			foreach($results as $file){
				$sub_results = $pcs->listFiles($file->path,'time','desc','0-10');
				$sub_results = json_decode($sub_results);
				$sub_results = $sub_results->list;
				if(!empty($sub_results)){
					$file_name = explode('/',$file->path);
					$file_name = $file_name[count($file_name)-1];
					$file_name = str_replace('_',' ',$file_name);
					$download_link = 'https://pcs.baidu.com/rest/2.0/pcs/file?method=download&access_token='.$access_token.'&path=';
					echo '<p>'.$file_name.':';
					foreach($sub_results as $sub){
						$sub_link = $download_link.urlencode($sub->path);
						$sub_name = explode('/',$sub->path);
						$sub_name = $sub_name[count($sub_name)-1];
						echo '<a href="'.$sub_link.'" target="wp_backup_to_pcs_opt_packs_iframe">'.$sub_name.'</a> ';
					}
					echo '<a href="https://pcs.baidu.com/rest/2.0/pcs/file?method=delete&access_token='.$access_token.'&path='.urlencode($file->path).'" target="wp_backup_to_pcs_opt_packs_iframe" onclick="wp_backup_to_pcs_delete(this);">删除</a>';
					echo '</p>';
				}
			}
			echo '</div>';
		}
	endif;
	?>
	<div class="inside" style="border-bottom:1px solid #CCC;margin:0;padding:8px 10px;">
		<?php if(!IS_WP2PCS_WRITABLE) : ?>
		<p style="color:red"><b>当前环境下/wp-content/目录没有可写权限，不能在线打包zip文件，请赋予这个目录可写权限！注：BAE和SAE本身不具备可写权限，因此本插件功能受限。</b></p>
		<?php endif; ?>
		<p>定时功能：选“永不”则不备份。定时功能基于wordpress的corn，只有在激活时定时任务才能被加入进程中，所以，如果你想要修改定时任务的周期或时间，你必须先关闭定时任务，接着修改，再开启，这样才能让新的定时任务生效。为了方便管理定时任务，建议你使用一款名为<a href="http://wordpress.org/plugins/wp-crontrol/" target="_blank">wp-crontrol</a>的插件来管理所有的定时任务，以了解本定时任务的进展。</p>
		<?php if(IS_WP2PCS_WRITABLE) : ?>
		<p style="color:red;font-weight:bold;">注意：由于备份时需要创建压缩文件，并把压缩文件上传到百度网盘，因此一方面需要你的网站空间有可写权限和足够的剩余空间，另一方面可能会消耗你的网站流量，因此请你一定要注意定时备份时选择合理的备份方式，以免造成空间塞满或流量耗尽等问题。</p>
		<p>境外主机受网络限制，使用马上备份功能可能面临失败的情况，请谨慎使用。<b>你可以选择“压缩下载”功能，它和马上备份的效果是一样的，只不过不自动上传到百度网盘，你需要下载下来自己上传到网盘。</b><p>
		<?php endif; ?>
		<?php if($app_key == 'false') : ?>
		<p>备份至网盘目录：由于你使用的是托管服务，因此，我们只能划出一个文件夹给你使用，你没有对这个文件夹的权限，唯一可以做的就是给你的文件夹取一个容易找到的名字，以方便日后下载备份资料。</p>
		<?php else : ?>
		<p>备份至网盘目录：你会在百度网盘的“我的应用数据”中看到“wp2pcs”这个目录，你填写“backup/”，就会在你的网盘目录“我的应用数据/wp2pcs/backup/”中找到自己的备份数据。<b>如果你打算把插件用在多个网站中，一定要注意通过设置不同的备份网盘目录，以区分不同的网站。</b></p>
		<?php endif; ?>
		<?php if(IS_WP2PCS_WRITABLE) : ?>
		<p>一般而言，网址的日志是以.log结束的，文件记录了网站被访问、蜘蛛抓取等信息。在上面填写日志文件夹的路径，留空则不备份日志。这个路径不是访问URL，而是相对于服务器的文件路径。你的网站的根路径是“<?php echo ABSPATH; ?>”，一般日志文件都存放在<?php echo ABSPATH; ?>logs/或和public_html目录同一个级别，你需要填写成你自己的。</p>
		<p>备份特定目录或文件：每行一个，当前年月日分别用{year}{month}{day}代替，不能有空格，末尾带/，必须为网站目录路径（包含路径头<?php echo ABSPATH; ?>）。<b>注意，上级目录将包含下级目录，如<?php echo ABSPATH; ?>wp-content/将包含<?php echo ABSPATH; ?>wp-content/uploads/，因此务必不要重复，两个只能填一个，否则会报错。</b>填写了目录或文件列表之后，只备份填写的列表中的目录或文件。不填，则不备份网站目录下的文件。</p>
		<?php endif; ?>
	</div>
	</form>
</div>
<?php
}