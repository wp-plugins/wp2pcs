<?php

/*
Plugin Name: WP2PCS(WP连接百度网盘)
Plugin URI: http://wp2pcs.duapp.com/
Description: 本插件帮助网站站长将网站和百度网盘连接。网站的数据库、日志、网站程序文件（包括wordpress系统文件、主题、插件、上传的附件等）一并上传到百度云盘，站长可以根据自己的习惯定时备份，让你的网站数据不再丢失！可以实现把网盘作为自己的附件存储空间，实现文件、图片、音乐、视频外链等功能。
Version: 1.2.0
Author: 否子戈
Author URI: http://www.utubon.com
*/

/*  Copyright 2013  否子戈  (email : frustigor@163.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
*/

// 初始化固定值常量
define('WP2PCS_PLUGIN_NAME',__FILE__);
define('WP2PCS_PLUGIN_VER','2013.12.21.11.00'); // 以最新一次更新的时间点（到分钟）作为版本号，注意以两位数字作为值
define('WP2PCS_APP_KEY','CuOLkaVfoz1zGsqFKDgfvI0h'); // WP2PCS官方API KEY
define('WP2PCS_ROOT_DIR','/apps/wp2pcs/');
define('WP2PCS_SUB_DIR',WP2PCS_ROOT_DIR.$_SERVER['SERVER_NAME'].'/');
define('WP2PCS_BACKUP_OFFLINE_SIZE',500*1024*1024);//采用离线备份方式减轻服务器压力时，问价大于多少bytes采用这种方式

// 包含一些必备的函数和类，以提供下面使用
require(dirname(__FILE__).'/wp2pcs-setup-functions.php');
require(dirname(__FILE__).'/libs/BaiduPCS.class.php');

// 经过判断或函数运算才能进行定义的常量
define('WP2PCS_APP_TOKEN',get_option('wp_to_pcs_access_token'));
define('IS_WP2PCS_WRITABLE',is_really_writable(WP_CONTENT_DIR));
if(!defined('WP_CONTENT_DIR')){
	define('WP_CONTENT_DIR',ABSPATH.'wp-content/');
}
if(get_option('wp_to_pcs_debug') == '开启调试'){
	define('WP2PCS_DEBUG',true);
}else{
	define('WP2PCS_DEBUG',false);
}
if(get_option('wp2pcs_connect_too_slow')=='true' && is_admin()){
	define('ALTERNATE_WP_CRON',true);// 防止定时任务丢失
}

// 直接初始化一个全局变量$baidupcs
$baidupcs = new BaiduPCS(WP2PCS_APP_TOKEN);

// 开启调试模式
if(WP2PCS_DEBUG){
	include(dirname(__FILE__).'/wp2pcs-debug.php');
}
// 下面是备份功能文件
require(dirname(__FILE__).'/wp-backup-database-functions.php');
require(dirname(__FILE__).'/wp-backup-file-functions.php');
require(dirname(__FILE__).'/wp-backup-to-baidu-pcs.php');
require(dirname(__FILE__).'/wp-diff-to-baidu-pcs.php');
// 下面是存储功能文件
require(dirname(__FILE__).'/wp-storage-image-outlink.php');
require(dirname(__FILE__).'/wp-storage-download-file.php');
require(dirname(__FILE__).'/wp-storage-video-online.php');
require(dirname(__FILE__).'/wp-storage-audio-online.php');
require(dirname(__FILE__).'/wp-storage-media-online.php');
require(dirname(__FILE__).'/wp-storage-to-baidu-pcs.php');
require(dirname(__FILE__).'/wp-storage-insert-to-content.php');

// 提高执行时间
add_filter( 'http_request_timeout', 'wp_smushit_filter_timeout_time');
function wp_smushit_filter_timeout_time($time) {
	$time = 25; //new number of seconds
	return $time;
}

// 默认设置选项
function wp_to_pcs_default_settings(){
	$app_key = get_option('wp_to_pcs_app_key');
	$root_dir = trailingslashit($app_key === 'false' ? WP2PCS_SUB_DIR : WP2PCS_ROOT_DIR.$_SERVER['SERVER_NAME']);
	if(!get_option('wp_backup_to_pcs_root_dir'))update_option('wp_backup_to_pcs_root_dir',$root_dir.'backup/');
	if(!get_option('wp_diff_to_pcs_root_dir'))update_option('wp_diff_to_pcs_root_dir',$root_dir);
	if(!get_option('wp_storage_to_pcs_root_dir'))update_option('wp_storage_to_pcs_root_dir',$root_dir.'wp-content/uploads/');
	if(!get_option('wp_storage_to_pcs_image_perfix'))update_option('wp_storage_to_pcs_image_perfix','?image');
	if(!get_option('wp_storage_to_pcs_download_perfix'))update_option('wp_storage_to_pcs_download_perfix','?download');
	if(!get_option('wp_storage_to_pcs_video_perfix'))update_option('wp_storage_to_pcs_video_perfix','index.php/video');
	if(!get_option('wp_storage_to_pcs_audio_perfix'))update_option('wp_storage_to_pcs_audio_perfix','?audio');
	if(!get_option('wp_storage_to_pcs_media_perfix'))update_option('wp_storage_to_pcs_media_perfix','?media');
	if(!get_option('wp_storage_to_pcs_outlink_type'))update_option('wp_storage_to_pcs_outlink_type','200');
	if(!get_option('wp_storage_to_pcs_outlink_protact'))update_option('wp_storage_to_pcs_outlink_protact','true');
}

// 停用插件的时候停止定时任务
//register_deactivation_hook(WP2PCS_PLUGIN_NAME,'wp2pcs_plugin_deactivate');
function wp2pcs_plugin_deactivate(){
	// 删除授权TOKEN
	delete_option('wp_to_pcs_app_key');
	delete_option('wp_to_pcs_access_token');
	// 关闭定时任务
	if(wp_next_scheduled('wp_backup_to_pcs_corn_task_database'))
		wp_clear_scheduled_hook('wp_backup_to_pcs_corn_task_database');
	if(wp_next_scheduled('wp_backup_to_pcs_corn_task_logs'))
		wp_clear_scheduled_hook('wp_backup_to_pcs_corn_task_logs');
	if(wp_next_scheduled('wp_backup_to_pcs_corn_task_www'))
		wp_clear_scheduled_hook('wp_backup_to_pcs_corn_task_www');
	if(wp_next_scheduled('wp_backup_to_pcs_corn_task_clear_files'))
		wp_clear_scheduled_hook('wp_backup_to_pcs_corn_task_clear_files');
	// 删除定时备份的按钮信息
	delete_option('wp_backup_to_pcs_future');
}

// 添加菜单，分清楚是否开启多站点功能
if(is_multisite()){
	add_action('network_admin_menu','wp_to_pcs_menu');
	function wp_to_pcs_menu(){
		add_plugins_page('WordPress连接百度云盘','WP2PCS','manage_network','wp2pcs','wp_to_pcs_pannel');
	}
}else{
	add_action('admin_menu','wp_to_pcs_menu');
	function wp_to_pcs_menu(){
		add_plugins_page('WordPress连接百度云盘','WP2PCS','edit_theme_options','wp2pcs','wp_to_pcs_pannel');
	}
}

// 添加提交更新动作
add_action('admin_init','wp_to_pcs_action');
function wp_to_pcs_action(){
	if(!is_admin() && !current_user_can('edit_theme_options'))return;
	if(is_multisite() && !current_user_can('manage_network')){
		return;
	}elseif(!current_user_can('edit_theme_options')){
		return;
	}
	// 提交授权
	if(!empty($_POST) && isset($_POST['page']) && $_POST['page'] == $_GET['page'] && isset($_POST['action']) && $_POST['action'] == 'wp_to_pcs_app_key'){
		check_admin_referer();
		$app_key = $_POST['wp_to_pcs_app_key'];
		if(!trim($app_key)){
			wp_die('请选择授权方式');
			exit;
		}
		update_option('wp_to_pcs_app_key',$app_key);
		$back_url = wp_to_pcs_wp_current_request_url(false).'?page='.$_GET['page'];
		$back_url = urlencode(wp_nonce_url($back_url)); // 回调网址
		$admin_email = urlencode(get_option('admin_email')); // 用以通知更新
		if($app_key  === 'false'){
			// 如果托管到WP2PCS官方，将执行下面的操作
			$token_url = 'http://wp2pcs.duapp.com/apply?from='.$back_url.'&key='.WP2PCS_APP_KEY.'&email='.$admin_email;
		}else{
			$token_url = 'http://wp2pcs.duapp.com/oauth?from='.$back_url.'&key='.WP2PCS_APP_KEY.'&email='.$admin_email;
		}
		$site_id = get_option('wp_to_pcs_site_id');
		if($site_id){
			$token_url .= "&site=$site_id";
		}
		wp_redirect($token_url);
		exit;
	}
	// 授权通过
	if(isset($_GET['wp_to_pcs_access_token']) && !empty($_GET['wp_to_pcs_access_token'])){
		check_admin_referer();
		$access_token = $_GET['wp_to_pcs_access_token'];
		$site_id = $_GET['site_id'];
		update_option('wp_to_pcs_access_token',$access_token);
		update_option('wp_to_pcs_site_id',$site_id);
		wp_to_pcs_default_settings();// 初始化各个推荐值
		wp_redirect(wp_to_pcs_wp_current_request_url(false).'?page='.$_GET['page'].'&time='.time());
		exit;
	}
	// 更新授权API KEY
	if(!empty($_POST) && isset($_POST['page']) && $_POST['page'] == $_GET['page'] && isset($_POST['action']) && $_POST['action'] == 'wp_to_pcs_app_key_update' && isset($_POST['wp_to_pcs_app_key_update']) && $_POST['wp_to_pcs_app_key_update'] == '更新授权'){
		check_admin_referer();
		wp2pcs_plugin_deactivate();// 更新授权API KEY跟停用插件是一样的
		wp_redirect(wp_to_pcs_wp_current_request_url(false).'?page='.$_GET['page'].'&time='.time());
		exit;
	}
	// 调试模式
	if(!empty($_POST) && isset($_POST['page']) && $_POST['page'] == $_GET['page'] && isset($_POST['wp_to_pcs_debug']) && !empty($_POST['wp_to_pcs_debug'])){
		check_admin_referer();
		update_option('wp_to_pcs_debug',$_POST['wp_to_pcs_debug']);
		wp_redirect(wp_to_pcs_wp_current_request_url(false).'?page='.$_GET['page'].'&time='.time());
		exit;
	}
	// 简易加速
	if(!empty($_POST) && isset($_POST['page']) && $_POST['page'] == $_GET['page'] && isset($_POST['wp_to_pcs_speed_control']) && !empty($_POST['wp_to_pcs_speed_control'])){
		check_admin_referer();
		$speed_control = $_POST['wp_to_pcs_speed_control'];
		update_option('wp_to_pcs_speed_control',$speed_control);
		if($speed_control=='简易加速'){
			update_option('wp2pcs_connect_too_slow','true');
		}else{
			delete_option('wp2pcs_connect_too_slow');
		}
		wp_redirect(wp_to_pcs_wp_current_request_url(false).'?page='.$_GET['page'].'&time='.time());
		exit;
	}
}

// 选项和菜单
function wp_to_pcs_pannel(){
	$app_key = get_option('wp_to_pcs_app_key');
	$btn_text_debug = (get_option('wp_to_pcs_debug') == '开启调试' ? '关闭调试' : '开启调试');
	$btn_class_debug = ($btn_text_debug == '开启调试' ? 'button-primary' : 'button');
	$btn_text_speed = (get_option('wp_to_pcs_speed_control') == '简易加速' ? '关闭加速' : '简易加速');
	$btn_class_speed = ($btn_text_speed == '简易加速' ? 'button-primary' : 'button');
?>
<div class="wrap" id="wp2pcs-admin-dashbord">
	<h2>WP2PCS WordPress连接到百度网盘<?php if($app_key === 'false'){echo '[WP2PCS官方托管]';} ?></h2>
	<div id="application-update-notice" class="updated hidden" data-version="<?php echo str_replace('.','',WP2PCS_PLUGIN_VER); ?>" data-nonce="<?php echo wp_create_nonce(); ?>" data-admin-url="<?php echo admin_url('/'); ?>"><p>如果你看到该信息，说明WP2PCS官方不能被正常访问，<?php if($app_key==='false'): ?>你的托管服务将受到限制，赶紧和我们联系吧！<?php elseif(!$app_key): ?>不能正常授权，请稍后再试！<?php else : ?>如果你使用的是附件“外链”访问方式，那么赶紧切换到“直链”访问方式暂时解决！<?php endif; ?></p></div>
    <div class="metabox-holder">
	<?php if(!is_wp_to_pcs_active()): ?>
		<div class="postbox">
		<form method="post" autocomplete="off">
			<h3>百度授权 <a href="javascript:void(0)" class="tishi-btn right">+</a></h3>
			<div class="inside" style="border-bottom:1px solid #CCC;margin:0;padding:8px 10px;">
				<p style="color:#008000;"><?php
					// 首先检查php环境
					echo "你的网站搭建在 ".PHP_OS." 操作系统的服务器上，WIN主机对插件支持欠佳，详细请阅读<a href='http://wp2pcs.duapp.com/270' target='_blank'>这篇文章</a>。<br />";
					$software = get_blog_install_software();
					if($software=='IIS'){
						echo "你的网站运行在 $software 服务器上，对插件支持欠佳，详细请阅读<a href='http://wp2pcs.duapp.com/270' target='_blank'>这篇文章</a>。<br />";	
					}
					if(!class_exists('ZipArchive')){
						echo "PHP不存在ZipArchive类，插件不能打包压缩，不能正常备份网站的文件，简易联系主机商启用它。<br />";
					}
					if(!function_exists('curl_exec')){
						echo "curl_exec函数不被支持，插件无法和PCS服务器通信，联系主机商启用完整的CURL模块。<br />";
					}
					echo "如果你的网站放在海外主机，很有可能运行缓慢，甚至运行错误。建议使用亚洲范围内的主机。<br />";
					echo "开启插件后建议先打开调试模式，进入前台，阅读你的网站的调试信息，以确保能够正常使用。";
				?></p>
			</div>
			<div class="inside" style="border-bottom:1px solid #CCC;margin:0;padding:8px 10px;">
				<p>
					<input type="radio" name="wp_to_pcs_app_key" value="true" <?php checked($app_key,'true'); ?> />保存于自己的网盘
					<input type="radio" name="wp_to_pcs_app_key" value="false" <?php checked($app_key,'false'); ?> />托管于WP2PCS官方
				</p>
				<p><input type="submit" value="提交" class="button-primary" /></p>
				<input type="hidden" name="action" value="wp_to_pcs_app_key" />
				<input type="hidden" name="page" value="<?php echo $_GET['page']; ?>" />
				<?php wp_nonce_field(); ?>
			</div>
			<div class="inside tishi hidden" style="border-bottom:1px solid #CCC;margin:0;padding:8px 10px;">
				<p>本插件需要你登录自己的百度账号，如果你还没有开通自己的百度网盘，或者不愿意占用自己的网盘空间，可以将自己的资料托管于WP2PCS官方网盘，WP2PCS官方承诺尽最大努力保护你的资料安全。</p>
			</div>
		</form>
		</div>
	<?php else : ?>
		<div class="postbox">
		<form method="post" autocomplete="off">
			<h3>百度授权更新 <a href="javascript:void(0)" class="tishi-btn right">+</a></h3>
			<div class="inside" style="border-bottom:1px solid #CCC;margin:0;padding:8px 10px;">
				<p class="tishi hidden">请及时关注<a href="http://wp2pcs.duapp.com">WP2PCS官方</a>发布的信息，如果官方通知要更新授权时，请及时更新授权，否则可能不能使用本插件。</p>
				<?php if($app_key === 'false') : ?><p>你当前使用的是托管到WP2PCS的服务，如果你已经拥有了自己的网盘，不妨更新授权。但需要注意的是，目前WP2PCS还没有开发一键转移功能，所以这些附件只能通过申请后邮件发送给你。</p><?php endif; ?>
				<p class="tishi hidden" id="wp2pcs-information-pend">更新授权前请注意：1、更新后老的授权信息会被直接删除；2、如果你开启了定时备份，请先关闭。</p>
				<p>
					<input type="submit" name="wp_to_pcs_app_key_update" value="更新授权" class="button-primary" onclick="if(!confirm('更新后会重置你填写的内容，如果重新授权，你需要再设置一下这些选项。是否确定更新？'))return false;" />
					<input type="submit" name="wp_to_pcs_debug" value="<?php echo $btn_text_debug; ?>" class="<?php echo $btn_class_debug; ?>" <?php if($btn_text_debug=='开启调试') : ?>onclick="if(!confirm('开启调试模式之后，前台将不能正常访问，而是会进入调试模式。是否确定？'))return false;"<?php endif; ?>/>
					<input type="submit" name="wp_to_pcs_speed_control" value="<?php echo $btn_text_speed; ?>" class="<?php echo $btn_class_speed; ?>" />
				</p>
				<p class="tishi hidden">开启调试：部分网站在运行wp2pcs的时候，会出现各种各样的问题，为了找到问题产生的根源，首先开启调试模式，这个时候前台无法正常访问，会直接显示调试信息，通过这些信息，可以判断插件的问题出现在什么地方。</p>
				<p class="tishi hidden">简易加速是针对一些服务器与PCS之间通信不良而设计的，开启简易加速之后，可以删除一些不必要的查询，从而加快插件的访问速度，适用于一些国外空间或访问特别慢的空间。</p>
				<input type="hidden" name="action" value="wp_to_pcs_app_key_update" />
				<input type="hidden" name="page" value="<?php echo $_GET['page']; ?>" />
				<?php wp_nonce_field(); ?>
			</div>
		</form>
		</div>
		<?php if(function_exists('wp_backup_to_pcs_panel'))wp_backup_to_pcs_panel(); ?>
		<?php if(function_exists('wp_backup_to_pcs_panel'))wp_diff_to_pcs_panel(); ?>
		<?php if(function_exists('wp_storage_to_pcs_panel'))wp_storage_to_pcs_panel(); ?>
		<div id="wp2pcs-information-area">
			<?php
				if(get_option('wp2pcs_connect_too_slow')=='true'):
					echo "<p>当前开启了简易加速，对网盘的连接、空间容量、当前正在进行的任务的查询都不会显示，从而节省资源提高访问速度。</p>";
				else :
					// 判断是否已经授权，如果quota失败的话，就可能需要重新授权
					global $baidupcs;
					$quota = json_decode($baidupcs->getQuota());
					if(!$baidupcs || !$quota || isset($quota->error_code) || $quota->error_code){
						if(get_option('wp_to_pcs_site_id')){
							echo '<p style="color:red;"><b>连接失败，有可能和百度网盘通信不良！</b></p>';
						}else{
							echo '<p style="color:red;"><b>可能由于授权问题，你的网站无法连接到百度网盘，点击“更新授权”再授权！</b></p>';				
						}
					}elseif($app_key != 'false'){
						echo '<p>当前网盘总'.number_format(($quota->quota/(1024*1024)),2).'MB，剩余'.number_format((($quota->quota - $quota->used)/(1024*1024)),2).'MB。请注意合理使用。</p>';
					}
					if(get_php_run_time() > 15){
						echo '<p style="color:red;font-weight:bold;">你当前的打开速度比较慢，有可能造成备份中断、图片显示慢甚至失败等问题，使用中请注意。为了找到缓解该问题的办法，你可以联系我们获得更高级别的服务。</p>';
					}
				endif;
			?>
		</div>
		<script>jQuery(function($){$('#wp2pcs-information-area').insertAfter('#wp2pcs-information-pend');});</script>
	<?php endif; ?>
		<div class="postbox">
			<h3>说明 <a href="javascript:void(0)" class="tishi-btn right">+</a></h3>
			<div class="inside tishi hidden" style="border-bottom:1px solid #CCC;margin:0;padding:8px 10px;">
				<p>本插件主要用于将WordPress和百度网盘连接起来，把百度网盘作为WordPress的后备箱。</p>
				<p>本插件主要希望实现以下目标：1、备份WordPress到百度网盘，以免网站数据丢失；2、WordPress中上传的附件等直接上传到百度网盘，并将网盘作为网站的下载空间，实现直链下载、图片外链、音乐视频外链等；3、开发更多的WP2PCS应用，例如可以通过百度网盘手机客户端就可以写文章等创意功能。但明显，功能还不够完善，如果你愿意，可以参与到我们的开发中，请进入下方给出的插件主页和我们联系。</p>
				<p><b style="color:red;">注意：由于插件使用的是百度PCS API，所以必须要考虑有关问题，使用前最好到<a href="http://wp2pcs.duapp.com">插件主页</a>了解使用方法，以免使用中出错。</b></p>
			</div>
			<div class="inside" style="border-bottom:1px solid #CCC;margin:0;padding:8px 10px;">
				<p>插件同时处于开发中，欢迎站长、博主朋友们向我们反馈，提出宝贵意见，或加入到开发中。</p>
				<p>官方网站：<a href="http://wp2pcs.duapp.com" target="_blank">http://wp2pcs.duapp.com</a></p>
				<p>QQ群：292172954 <a href="http://shang.qq.com/wpa/qunwpa?idkey=97278156f3def92eef226cd5b88d9e7a463e157655650f4800f577472c219786" target="_blank"><img title="WP2PCS官方交流群" alt="WP2PCS官方交流群" src="http://pub.idqqimg.com/wpa/images/group.png" border="0" /></a></p>
				<p>向插件作者捐赠：<a href="http://me.alipay.com/tangshuang" target="_blank">支付宝</a>、BTC（164jDbmE8ncUYbnuLvUzurXKfw9L7aTLGD）、PPC（PNijEw4YyrWL9DLorGD46AGbRbXHrtfQHx）、XPM（AbDGH5B7zFnKgMJM8ujV3br3R2V31qrF2F） <a href="http://wp2pcs.duapp.com/240" target="_blank" title="WP2PCS为何支持BTC、PPC、XPM捐赠且只支持这三种币？">?</a></p>
			</div>
			<div class="inside" style="border-bottom:1px solid #CCC;margin:0;padding:8px 10px;">
				<p><b>最新动态</b></p>
				<div style="width:90%;height:260px;overflow:hidden;text-align:center;line-height:260px;background:#ccc;">
					<a href="javascript:void(0)" id="open-wp2pcs-notic-in-iframe">点击查看</a>
					<a href="http://wp2pcs.duapp.com/category/%e5%8a%a8%e6%80%81%e6%9b%b4%e6%96%b0" target="_blank">直接阅读</a>
					<script>
					jQuery(function($){
						$('#open-wp2pcs-notic-in-iframe').click(function(){
							$(this).parent().css('background','none');
							$(this).html('<iframe src="http://wp2pcs.duapp.com/category/%e5%8a%a8%e6%80%81%e6%9b%b4%e6%96%b0" frameborder="0" style="width:100%;height:610px;margin-top:-230px;margin-left:-20px;"></iframe>');
						});
					});
					</script>
				</div>
			</div>
		</div>
    </div>
</div>
<script>
jQuery(function($){
	$('a.tishi-btn').attr('title','点击了解该功能的具体用途').css('text-decoration','none').toggle(function(){
		$(this).parent().parent().find('.tishi').show();
		$(this).text('-');
	},function(){
		$(this).parent().parent().find('.tishi').hide();
		$(this).text('+');
	});
});
</script>
<script src="http://wp2pcs.duapp.com/application-update-notice.js?ver=<?php set_php_ini('timezone');echo date('Y-m-d-H'); ?>" charset="utf-8"></script>
<script>
jQuery('#wp2pcs-admin-notice').remove();
jQuery('#application-update-notice').show();
</script>
<?php
}