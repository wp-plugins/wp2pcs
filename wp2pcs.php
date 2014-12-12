<?php

/*
Plugin Name: WP2PCS
Plugin URI: http://www.wp2pcs.com/
Description: 本插件帮助网站站长将网站和百度网盘连接。网站定时备份，调用网盘资源在网站中使用。
Version: 1.4.0
Author: 否子戈
Author URI: http://www.utubon.com
*/

date_default_timezone_set('PRC');
define('WP2PCS_PLUGIN_NAME',__FILE__);

// 包含一些必备的函数和类，以提供下面使用
require 'libs/BaiduPCS.class.php';
require 'libs/FileZip.class.php';
require 'libs/DbZip.class.php';
require 'libs/functions.backup.php';
require 'config.php';

// 直接初始化全局变量
$BaiduPCS = new BaiduPCS(BAIDUPCS_ACCESS_TOKEN);
$FileZip = new FileZip;
$DbZip = new DbZip(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);

class WP2PCS {
  function __construct() {
    add_action('init',array($this,'menu_init'));
    add_action('admin_init',array($this,'action'));
  }
  function menu_init() {
    if((is_multisite() && !current_user_can('manage_network')) || (!is_multisite() && !current_user_can('edit_theme_options'))) return;
    if(is_multisite()) add_action('network_admin_menu',array($this,'add_menu'));
    else add_action('admin_menu',array($this,'add_menu'));
  }
  function add_menu() {
    $this->scripts_init();
    if(is_multisite())add_plugins_page('WordPress连接云盘','WP2PCS','manage_network','wp2pcs',array($this,'menu_page'));
    else add_plugins_page('WordPress连接云盘','WP2PCS','edit_theme_options','wp2pcs',array($this,'menu_page'));
  }
  function menu_page() {
    $tab = isset($_GET['tab']) && !empty($_GET['tab']) ? $_GET['tab'] : 'default';
    $file = dirname(WP2PCS_PLUGIN_NAME)."/admin/$tab.php";
    if(file_exists($file)) include($file);
  }
  function action() {
    $tab = isset($_GET['tab']) && !empty($_GET['tab']) ? $_GET['tab'] : 'default';
    $file = dirname(WP2PCS_PLUGIN_NAME)."/action/$tab.php";
    if(file_exists($file)) include($file);
  }
  function scripts_init() {
    if(@$_GET['page'] == 'wp2pcs') {
      add_action('admin_enqueue_scripts',array($this,'scripts_init'));
    }
  }
  function add_scripts() {
    wp_register_script('wp2pcs_script',plugins_url('/assets/javascript.js',WP2PCS_PLUGIN_NAME));
    wp_enqueue_script('wp2pcs_script');
  }
}
$WP2PCS = new WP2PCS;

// 谷歌被墙，后台字体加载慢，禁用
class Disable_Google_Fonts {
  public function __construct() {
    add_filter( 'gettext_with_context', array( $this, 'disable_open_sans' ), 888, 4 );
  }
  public function disable_open_sans( $translations, $text, $context, $domain ) {
    if ( 'Open Sans font: on or off' == $context && 'on' == $text ) {
      $translations = 'off';
    }
    return $translations;
  }
}
$disable_google_fonts = new Disable_Google_Fonts();

$function_files_path = dirname(WP2PCS_PLUGIN_NAME).'/hook';
if(file_exists($function_files_path)):
$function_files = scandir($function_files_path);
if($function_files){
  foreach($function_files as $function_file)
    if(substr($function_file,-4) == '.php')
      include_once($function_files_path.'/'.$function_file);
}
endif;
