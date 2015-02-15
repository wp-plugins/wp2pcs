<?php

// 百度云
define('BAIDUPCS_ACCESS_TOKEN',get_option('wp2pcs_baidupcs_access_token'));
define('BAIDUPCS_REMOTE_ROOT','/apps/wp2pcs/'.substr(home_url(),strpos(home_url(),'://')+3));

// 腾讯及微云
define('TENCENT_APP_ID',get_option('wp2pcs_tencent_app_id'));
define('TENCENT_OPEN_ID',get_option('wp2pcs_tencent_open_id'));
define('TENCENT_ACCESS_TOKEN',get_option('wp2pcs_tencent_access_token'));
define('WEIYUN_REMOTE_ROOT','/wp2pcs/'.substr(home_url(),strpos(home_url(),'://')+3));

// 本地
define('WP2PCS_TEMP_DIR',dirname(__FILE__).DIRECTORY_SEPARATOR.'temp.dir');
define('WP2PCS_CACHE_DIR',dirname(__FILE__).DIRECTORY_SEPARATOR.'cache.dir');
define('WP2PCS_CACHE_COUNT',20);// 某一个附件被访问N次后缓存在本地

// 当你发现自己错过了很多定时任务时，可以帮助你执行没有执行完的定时任务
//define('ALTERNATE_WP_CRON',true);
