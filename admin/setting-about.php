<style>
.update-about-feature {
  padding:20px 0;
}
.update-about-feature h3 {
  text-align: center;
  margin: 20px 0;
}
.update-about-feature h4 {
  text-align: center;
}
.update-about-feature img {
  max-width: 100%;
  height: auto;
}
.headline-feature {
  text-align: center;
}
.headline-feature h2 {
  margin: 30px 0;
}
.feature-section .col {
  float: left;
}
.feature-section.two-col .col {
  width: 48%;
  margin-right: 4%;
}
.feature-section.three-col .col {
  width: 30%;
  margin-right: 5%;
}
.feature-section .last-col {
  margin-right: 0 !important;
}
</style>

<div class="wrap">

<h2 class="nav-tab-wrapper">
  <a href="<?php menu_page_url('wp2pcs-setting'); ?>" class="nav-tab">基本信息</a>
  <a href="<?php echo add_query_arg('tab','load',menu_page_url('wp2pcs-setting',false)); ?>" class="nav-tab">资源调用</a>
  <a href="<?php echo add_query_arg('tab','backup',menu_page_url('wp2pcs-setting',false)); ?>" class="nav-tab">定时备份</a>
  <a href="javascript:void(0);" class="nav-tab nav-tab-active">关于</a>
</h2>

<div class="update-about-feature headline-feature">
	<h2>全新的1.4.0版本，为你带来超简化的酷爽体验！</h2>
	<?php if(get_option('wp_to_pcs_app_token')) { echo '<p style="color: #DA4F25;">检测到你是从低版本升级上来的，1.4.0版本是一个全新的版本，以前的所有设置都无效了，你需要花点时间来看下怎么升级。点击<a href="http://www.wp2pcs.com/?p=432" target="_blank">这里</a>查看</p>'; } ?>
	<div class="featured-image">
		<img class="about-overview-img" src="<?php echo plugins_url('assets/about.png',WP2PCS_PLUGIN_NAME); ?>" width="640" height="360" />
	</div>
	<div class="clear"></div>
</div>

<hr />

<div class="update-about-feature">
	<div class="feature-section two-col">
    <div class="col col-1">
      <h3>更简洁的设置和操作</h3>
      <p>后台的设置操作被简化的更加便捷，更加人性化，我们不要追求复杂，而是简化。</p>
    </div>
    <div class="col col-2 last-col">
      <img src="<?php echo plugins_url('assets/about-1.jpg',WP2PCS_PLUGIN_NAME); ?>" />
    </div>
  </div>
  <div class="clear"></div>
</div>

<hr />

<div class="update-about-feature">
	<div class="feature-section two-col">
    <div class="col col-1">
      <img src="<?php echo plugins_url('assets/about-2.jpg',WP2PCS_PLUGIN_NAME); ?>" />
    </div>
    <div class="col col-2 last-col">
      <h3>更酷炫的媒体插入界面</h3>
      <p>优化了媒体插入界面，去除繁复，留下最好看的界面。</p>
    </div>
  </div>
  <div class="clear"></div>
</div>

<hr />

<div class="update-about-feature">
  <h3>核心功能</h3>
  <div class="feature-section three-col">
    <div class="col col-1">
      <h4>调用云盘资源</h4>
      <p>把云盘当做你的存储空间，调用云盘资源到网站里面用。</p>
    </div>
    <div class="col col-2">
      <h4>定时自动备份</h4>
      <p>在设定的时间自动备份到云盘，让你的网站文件和数据解除风险。</p>
    </div>
    <div class="col col-3 last-col">
      <h4>付费用户</h4>
      <p>成为付费用户，只需108元/年，专享付费特殊服务。</p>
    </div>
  </div>
  <div class="clear"></div>
</div>

</div>
