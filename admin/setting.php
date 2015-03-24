<div class="wrap">

<h2 class="nav-tab-wrapper">
  <a href="javascript:void(0)" class="nav-tab nav-tab-active">基本信息</a>
  <a href="<?php echo add_query_arg('tab','load',menu_page_url('wp2pcs-setting',false)); ?>" class="nav-tab">资源调用</a>
  <a href="<?php echo add_query_arg('tab','backup',menu_page_url('wp2pcs-setting',false)); ?>" class="nav-tab">定时备份</a>
</h2>

<div class="metabox-holder"><div class="meta-box-sortables">

<div class="postbox">
  <div class="handlediv" title="点击以切换"><br></div>
  <h3 class="hndle">授权信息</h3>
  <div class="inside">
    <?php
      if(!BAIDUPCS_ACCESS_TOKEN) {
        $baidupcs_btn_class = 'button-primary';
      }
      else {
        $baidupcs_btn_class = 'button';
        global $BaiduPCS;
        $quota = json_decode($BaiduPCS->getQuota());
        if(isset($quota->error_code) || $quota->error_code || (int)$quota->quota == 0){
          echo '<p>获取百度网盘容量错误，重新授权试试吧。</p>';
          $baidupcs_btn_class = 'button-primary';
        }
        else{
          echo '<p>当前百度网盘总'.number_format(($quota->quota/(1024*1024)),2).'MB，剩余'.number_format((($quota->quota - $quota->used)/(1024*1024)),2).'MB。</p>';
        }
      }
      if(!TENCENT_OPEN_ID || !TENCENT_ACCESS_TOKEN) {
        $weiyun_btn_class = 'button-primary';
      }
      else {
        $weiyun_btn_class = 'button';
      }
    ?>
    <p>
      <a class="<?php echo $baidupcs_btn_class; ?>" onclick="window.location.href = 'https://api.wp2pcs.com/oauth_baidupcs.php?url=' + encodeURIComponent(window.location.href);">百度授权</a>
      <a class="<?php echo $weiyun_btn_class; ?>" onclick="window.location.href = 'https://api.wp2pcs.com/oauth_weiyun.php?url=' + encodeURIComponent(window.location.href);">微云授权</a> <small>微云授权暂时没用</small>
    </p>
  </div>
</div>

<div class="postbox">
  <div class="handlediv" title="点击以切换"><br></div>
  <h3 class="hndle">简要说明</h3>
  <div class="inside">
    <p>WP2PCS当前版本：<?php echo WP2PCS_PLUGIN_VERSION; ?> <a href="http://www.wp2pcs.com/?cat=1" target="_blank" class="button">查看插件更新</a></p>
    <p>官方网站：<a href="http://www.wp2pcs.com" target="_blank">http://www.wp2pcs.com</a></p>
    <p>交流QQ群：<a href="http://shang.qq.com/wpa/qunwpa?idkey=97278156f3def92eef226cd5b88d9e7a463e157655650f4800f577472c219786" target="_blank">292172954</a></p>
    <p>作者：<a href="http://weibo.com/hz184" target="_blank">@否子戈</a>，网站：<a href="http://www.utubon.com" target="_blank">乌徒帮</a></p>
    <p>向作者捐赠：支付宝<code>476206120@qq.com</code>，财付通<code>476206120</code>，比特币<code>16tezAsqEZHhVz4xAmNAp8A5r7qMg1NG9P</code>，多少都没有关系，捐赠的时候请注明一下你是要捐赠“WP2PCS WP插件”。也可以选择<a href="http://www.wp2pcs.com/?page_id=730" target="_blank">付费服务</a>，获得更多功能。</p>
  </div>
</div>

</div></div><!-- // -->

</div>
