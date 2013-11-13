<?php

/*
Plugin Name: WP2PCS (WP TO BAIDU PCS)
Plugin URI: http://wordpress.org/plugins/wp2pcs/
Description: 本插件帮助网站站长将网站和百度网盘连接。网站的数据库、日志、网站程序文件（包括wordpress系统文件、主题、插件、上传的附件等）一并上传到百度云盘，站长可以根据自己的习惯定时备份，让你的网站数据不再丢失！可以实现把网盘作为自己的附件存储空间，实现文件、图片、音乐、视频外链等功能。
Version: 1.1
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
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('WP2PCS_PLUGIN_NAME',__FILE__);
define('WP2PCS_API_KEY','CuOLkaVfoz1zGsqFKDgfvI0h'); // WP2PCS官方API KEY
define('WP2PCS_ROOT_DIR','/apps/wp2pcs/'); // 应用在网盘中的位置
define('WP2PCS_SUB_DIR',WP2PCS_ROOT_DIR.$_SERVER['SERVER_NAME'].'/'); // 如果使用WP2PCS托管服务的话

require(dirname(__FILE__).'/libs/BaiduPCS.class.php');
require(dirname(__FILE__).'/wp-backup-database-functions.php');
require(dirname(__FILE__).'/wp-backup-file-functions.php');
require(dirname(__FILE__).'/wp-backup-to-baidu-pcs.php');
require(dirname(__FILE__).'/wp-storage-to-baidu-pcs.php');
require(dirname(__FILE__).'/wp-storage-image-outlink.php');
require(dirname(__FILE__).'/wp-storage-download-file.php');

// 创建一个函数，用来判断是否已经授权
function is_wp_to_pcs_token_active(){
	$access_token = get_option('wp_to_pcs_access_token');
	$pcs = new BaiduPCS($access_token);
	$quota = json_decode($pcs->getQuota());
	if(!$access_token || empty($access_token) || !$pcs || !$quota || isset($quota->error_code) || $quota->error_code){
		return false;
	}
	return true;
}

// 因为下面的文件需要上面的函数，所以放到最后
include(dirname(__FILE__).'/wp-storage-insert-to-content.php');

// 获取当前访问的URL地址
function wp_to_pcs_wp_current_request_url($query = array(),$remove = array()){
	// http://www.ludou.org/how-to-get-the-current-url-in-wordpress.html
	global $wp;
	$home_root = substr_count(home_url(),'/') <= 3 ? true : false;
	$permalink = trim(get_option('permalink_structure')) != '' ? true : false;
	if($home_root){
		$current_url = home_url(add_query_arg(array()));
	}
	if(!$home_root && $permalink){
		$current_url = home_url(add_query_arg(array(),$wp->request));
	}
	if(!$home_root && !$permalink){
		$current_url = add_query_arg($wp->query_string,'',home_url($wp->request));
	}
	if($current_url == home_url()){
		$current_url = home_url('/');
	}
	$parse_url = parse_url($current_url);
	if(is_array($query) && !empty($query)){
		parse_str($parse_url['query'],$parse_query);
		$parse_query = array_merge($parse_query,$query);
		if(!empty($remove))foreach($remove as $key){
			if(isset($parse_query[$key]))unset($parse_query[$key]);
		}
		$parse_query = http_build_query($parse_query);
		$current_url = str_replace($parse_url['query'],'?'.$parse_query,$current_url);
	}elseif($query === false){
		$current_url = str_replace('?'.$parse_url['query'],'',$current_url);
	}
	return $current_url;
}

// 添加更新动作
add_action('init','wp_to_pcs_action');
function wp_to_pcs_action(){
	if(!is_admin())return;
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
		if($app_key  == 'false'){
			// 如果托管到WP2PCS官方，将执行下面的操作
			$token_url = 'http://wp2pcs.duapp.com/apply?from='.$back_url.'&key='.WP2PCS_API_KEY.'&email='.$admin_email;
		}else{
			$token_url = 'http://wp2pcs.duapp.com/oauth?from='.$back_url.'&key='.WP2PCS_API_KEY.'&email='.$admin_email;
		}
		wp_redirect($token_url);
		exit;
	}
	// 授权通过
	if(isset($_GET['wp_to_pcs_access_token']) && !empty($_GET['wp_to_pcs_access_token'])){
		check_admin_referer();
		$app_key = get_option('wp_to_pcs_app_key');
		$root_dir = ($app_key == 'false' ? WP2PCS_SUB_DIR : WP2PCS_ROOT_DIR);
		$access_token = $_GET['wp_to_pcs_access_token'];
		update_option('wp_to_pcs_access_token',$access_token);
		update_option('wp_backup_to_pcs_root_dir',trailingslashit($root_dir).'backup/');
		update_option('wp_storage_to_pcs_root_dir',trailingslashit($root_dir).'uploads/');
		update_option('wp_storage_to_pcs_outlink_perfix','image');
		update_option('wp_storage_to_pcs_download_perfix','download');
		update_option('wp_storage_to_pcs_outlink_type','200');
		wp_redirect(remove_query_arg('wp_to_pcs_access_token'));
		exit;
	}
	// 更新API KEY
	if(!empty($_POST) && isset($_POST['page']) && $_POST['page'] == $_GET['page'] && isset($_POST['action']) && $_POST['action'] == 'wp_to_pcs_app_key_update' && isset($_POST['wp_to_pcs_app_key_update']) && $_POST['wp_to_pcs_app_key_update'] == '更新'){
		check_admin_referer();
		delete_option('wp_to_pcs_access_token');
		delete_option('wp_backup_to_pcs_root_dir');
		delete_option('wp_storage_to_pcs_root_dir');
		delete_option('wp_storage_to_pcs_outlink_perfix');
		delete_option('wp_storage_to_pcs_download_perfix');
		delete_option('wp_storage_to_pcs_outlink_type');
		wp_redirect(add_query_arg('time',time()));
		exit;
	}
}

// 添加菜单
add_action('admin_menu','wp_to_pcs_menu');
function wp_to_pcs_menu(){
	// 在wordpress菜单中添加插件菜单
	add_plugins_page('WordPress连接百度云盘','WP2PCS','edit_theme_options','wp2pcs','wp_to_pcs_pannel');
}

// 选项和菜单
function wp_to_pcs_pannel(){
	$app_key = get_option('wp_to_pcs_app_key');
?>
<div class="wrap" id="wonderful-links-seo-admin">
	<h2>WP2PCS WordPress连接到百度网盘</h2>
	<br class="clear" />
    <div class="metabox-holder">
	<?php if(!is_wp_to_pcs_token_active()): ?>
		<div class="postbox">
		<form method="post" autocomplete="off">
			<h3>百度授权</h3>
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
			<div class="inside" style="border-bottom:1px solid #CCC;margin:0;padding:8px 10px;">
				<p>本插件需要你登录自己的百度账号，如果你还没有开通自己的百度网盘，或者不愿意占用自己的网盘空间，可以将自己的资料托管于WP2PCS官方网盘，WP2PCS官方承诺尽最大努力保护你的资料安全。</p>
			</div>
		</form>
		</div>
	<?php else : ?>
		<?php if(function_exists('wp_backup_to_pcs_panel'))wp_backup_to_pcs_panel(); ?>
		<?php if(function_exists('wp_storage_to_pcs_panel'))wp_storage_to_pcs_panel(); ?>
		<div class="postbox">
		<form method="post" autocomplete="off">
			<h3>百度授权更新</h3>
			<div class="inside" style="border-bottom:1px solid #CCC;margin:0;padding:8px 10px;">
				<p>请及时关注<a href="http://wp2pcs.duapp.com">WP2PCS官方</a>发布的信息，如果官方通知要更新时，请及时更新，否则可能不能使用本插件。</p>
				<?php if($app_key == 'false') : ?><p>你当前使用的是托管到WP2PCS的服务，如果你已经拥有了自己的网盘，不妨更新授权。但需要注意的是，目前WP2PCS还没有开发一键转移功能，所以这些附件只能通过申请后邮件发送给你。</p><?php endif; ?>
				<p>更新前请注意：1、更新后老的授权信息会被直接删除；2、如果你开启了定时备份，请先关闭。</p>
				<p><input type="submit" name="wp_to_pcs_app_key_update" value="更新" class="button-primary" onclick="if(!confirm('更新后会重置你填写的内容，如果重新授权，你需要再设置一下这些选项。是否确定更新？'))return false;" /></p>
				<input type="hidden" name="action" value="wp_to_pcs_app_key_update" />
				<input type="hidden" name="page" value="<?php echo $_GET['page']; ?>" />
				<?php wp_nonce_field(); ?>
			</div>
		</form>
		</div>
	<?php endif; ?>
		<div class="postbox">
			<h3>说明</h3>
			<div class="inside" style="border-bottom:1px solid #CCC;margin:0;padding:8px 10px;">
				<p>本插件主要用于将WordPress和百度网盘连接起来，把百度网盘作为WordPress的后备箱。</p>
				<p>本插件主要希望实现以下目标：1、备份WordPress到百度网盘，以免网站数据丢失；2、WordPress中上传的附件等直接上传到百度网盘，并将网盘作为网站的下载空间，实现直链下载、图片外链、音乐视频外链等；3、开发更多的WP2PCS应用，例如可以通过百度网盘手机客户端就可以写文章等创意功能。但明显，功能还不够完善，如果你愿意，可以参与到我们的开发中，请进入下方给出的插件主页和我们联系。</p>
				<p><b>注意：由于插件使用的是百度PCS API，所以必须要考虑有关问题，使用前最好到<a href="http://wp2pcs.duapp.com">插件主页</a>了解使用方法，以免使用中出错。</b></p>
			</div>
			<div class="inside" style="border-bottom:1px solid #CCC;margin:0;padding:8px 10px;">
				<p>插件处于开发阶段，欢迎站长、博主朋友们向我们反馈，提出宝贵意见。</p>
				<p>插件主页：http://wp2pcs.duapp.com</p>
				<p>向插件作者捐赠：<a href="http://me.alipay.com/tangshuang">支付宝</a>、BitCoin（164jDbmE8ncUYbnuLvUzurXKfw9L7aTLGD）</p>
			</div>
		</div>
    </div>
</div>
<?php
}