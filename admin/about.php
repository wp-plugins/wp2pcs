<div class="wrap about-wrap">

<h2 class="nav-tab-wrapper">
  <a href="<?php echo admin_url('plugins.php?page=wp2pcs'); ?>" class="nav-tab">基本信息</a>
  <a href="<?php echo admin_url('plugins.php?page=wp2pcs&tab=backup'); ?>" class="nav-tab">定时备份</a>
  <a href="<?php echo admin_url('plugins.php?page=wp2pcs&tab=load'); ?>" class="nav-tab">资源调用</a>
  <a href="<?php echo admin_url('plugins.php?page=wp2pcs&tab=payfor'); ?>" class="nav-tab">付费</a>
  <a href="javascript:void(0);" class="nav-tab nav-tab-active">关于</a>
</h2>

<div class="changelog point-releases">
  <h3>全新的1.4.0版本，为你带来超简化的酷爽体验！</h3>
  <p>WP2PCS 1.4.0版是一个全新的版本，摒弃了以前所有版本，重构代码，实现性能和使用的体验提升。</p>
  <?php if(get_option('wp_to_pcs_app_token')) { echo '<p style="color: #DA4F25;">检测到你是从低版本升级上来的，1.4.0版本是一个全新的版本，以前的所有设置都无效了，你需要花点时间来看下怎么升级。点击<a href="http://www.wp2pcs.com/?p=432" target="_blank">这里</a>查看</p>'; } ?>
</div>

<div class="changelog">
  <div class="about-overview"><img class="about-overview-img" src="<?php echo plugins_url('assets/about.png',WP2PCS_PLUGIN_NAME); ?>" width="640" height="360" /></div>
  <hr />
  <div class="feature-section col two-col">
    <div class="col-1">
      <h3>更简洁的设置和操作</h3>
      <p>后台的设置操作被简化的更加便捷，更加人性化，我们不要追求复杂，而是简化。</p>
    </div>
    <div class="col-2 last-feature">
      <img src="<?php echo plugins_url('assets/about-1.jpg',WP2PCS_PLUGIN_NAME); ?>" />
    </div>
  </div>
  <hr />
  <div class="feature-section col two-col">
    <div class="col-1">
      <img src="<?php echo plugins_url('assets/about-2.jpg',WP2PCS_PLUGIN_NAME); ?>" />
    </div>
    <div class="col-2 last-feature">
      <h3>更酷炫的媒体插入界面</h3>
      <p>优化了媒体插入界面，去除繁复，留下最好看的界面。</p>
    </div>
  </div>
  <hr>
</div>

<div class="changelog under-the-hood">
  <h3>核心功能</h3>
  <div class="feature-section col three-col">
    <div>
      <h4>定时自动备份</h4>
      <p>在设定的时间自动备份到云盘，让你的网站文件和数据解除风险。</p>
    </div>
    <div>
      <h4>调用云盘资源</h4>
      <p>把云盘当做你的存储空间，调用云盘资源到网站里面用。</p>
    </div>
    <div class="last-feature">
      <h4>付费用户</h4>
      <p>成为付费用户，只需108元/年，专享付费特殊服务。</p>
    </div>
  </div>
</div>


</div>