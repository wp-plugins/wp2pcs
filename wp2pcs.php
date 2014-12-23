<?php

/*
Plugin Name: WP2PCS
Plugin URI: http://www.wp2pcs.com/
Description: 本插件帮助网站站长将网站和百度网盘连接。网站定时备份，调用网盘资源在网站中使用。
Version: 1.4.2
Author: 否子戈
Author URI: http://www.utubon.com
*/

date_default_timezone_set('PRC');
define('WP2PCS_PLUGIN_NAME',__FILE__);

// 包含一些必备的函数和类，以提供下面使用
require 'config.php';
require 'libs/functions.lib.php';
require 'libs/BaiduPCS.class.php';
require 'libs/FileZip.class.php';
require 'libs/DbZip.class.php';
require 'libs/functions.backup.php';

// 直接初始化全局变量
$BaiduPCS = new BaiduPCS(BAIDUPCS_ACCESS_TOKEN);
$FileZip = new FileZip;
$DbZip = new DbZip(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);

class WP2PCS {
  function __construct() {
    add_action('init',array($this,'init'));
  }
  function init() {
    if((is_multisite() && !current_user_can('manage_network')) || (!is_multisite() && !current_user_can('edit_theme_options'))) return;
    if(is_multisite()) add_action('network_admin_menu',array($this,'add_menu'));
    else add_action('admin_menu',array($this,'add_menu'));
    add_action('admin_init',array($this,'action'));
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
    if((is_multisite() && !current_user_can('manage_network')) || (!is_multisite() && !current_user_can('edit_theme_options'))) return;
    $tab = isset($_GET['tab']) && !empty($_GET['tab']) ? $_GET['tab'] : 'default';
    $file = dirname(WP2PCS_PLUGIN_NAME)."/action/$tab.php";
    if(file_exists($file)) include($file);
  }
  function scripts_init() {
    if(@$_GET['page'] == 'wp2pcs') {
      add_action('admin_enqueue_scripts',array($this,'add_scripts'));
    }
  }
  function add_scripts() {
    wp_register_script('wp2pcs_script',plugins_url('/assets/javascript.js',WP2PCS_PLUGIN_NAME));
    wp_enqueue_script('wp2pcs_script');
  }
}
$WP2PCS = new WP2PCS;

$hook_dir = dirname(WP2PCS_PLUGIN_NAME).'/hook';
if(is_dir($hook_dir)) :
$hook_files = scandir($hook_dir);
if($hook_files){
  foreach($hook_files as $hook_file)
    if(substr($hook_file,-4) == '.php')
      include_once($hook_dir.'/'.$hook_file);
}
endif;
