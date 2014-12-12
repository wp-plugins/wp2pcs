<div class="wrap">

<h2 class="nav-tab-wrapper">
  <a href="<?php echo admin_url('plugins.php?page=wp2pcs'); ?>" class="nav-tab">基本信息</a>
  <a href="<?php echo admin_url('plugins.php?page=wp2pcs&tab=backup'); ?>" class="nav-tab">定时备份</a>
  <a href="<?php echo admin_url('plugins.php?page=wp2pcs&tab=load'); ?>" class="nav-tab">资源调用</a>
  <a href="<?php echo admin_url('plugins.php?page=wp2pcs&tab=payfor'); ?>" class="nav-tab">付费</a>
  <a href="javascript:void(0)" class="nav-tab nav-tab-active">外链设置</a>
</h2>

<div class="metabox-holder"><div class="meta-box-sortables">
<form method="post">

<?php if(!BAIDUPCS_ACCESS_TOKEN) { ?>
<div class="error"><p><strong>提示</strong>：还没有百度授权。</p></div>
<?php } ?>

<div class="postbox">
  <div class="handlediv" title="点击以切换"><br></div>
  <h3 class="hndle">你的信息</h3>
  <div class="inside"><?php $wp2pcs_outlink_code = get_option('wp2pcs_outlink_code');$wp2pcs_site_id = get_option('wp2pcs_site_id');  ?>
    <p>当前站点：<input type="text" value="<?php echo substr(home_url(),strpos(home_url(),'://')+3); ?>" class="regular-text" readonly></p>
    <p>外链码：<input type="text" name="wp2pcs_outlink_code" value="<?php echo $wp2pcs_outlink_code; ?>"></p>
    <?php if($wp2pcs_site_id) { ?><p>站点外链形式：http://static.wp2pcs.com/<?php echo $wp2pcs_site_id; ?>/dir/file.jpg</p><?php } ?>
  </div>
</div>

<div class="postbox">
  <div class="handlediv" title="点击以切换"><br></div>
  <h3 class="hndle">必读说明</h3>
  <div class="inside">
    <p>该功能只有付费会员才可以享受，一个站点只对应一个外链码。付费会员最多可以享受20个外链码（即20个站点，站点是指独立的网站、子域名网站、子目录中的网站等），超出部分需另外付费。另外，一个站点的流量也不应太大，不能超过所有会员所有站点流量平均数的20%，不然可能导致外链资源的隐患。</p>
    <p>使用外链可以节省流量、加快附件加载或下载速度、获得永久稳定的外链URL。</p>
    <h4>如何获取“外链码”</h4>
    <p>联系<a href="http://weibo.com/hz184" target="_blank">@否子戈</a> 购买成为WP2PCS会员（会员费108元/年），付费后即可拥有<a href="http://www.utubon.com" target="_blank">乌徒坛</a>的用户权限，阅读WP2PCS外链码获取和使用方法。</p>
    <h4>如何使用</h4>
    <p>获取外链码之后，只需要使用如<code>http://static.wp2pcs.com/外链码/test.jpg</code>这样的URL调用文件。当然，你站内的调用仍然有效。你还可以在“资源调用”中设置调用链接的形式。</p>
  </div>
</div>

<button type="submit" class="button-primary">确定</button>
<a href="http://www.wp2pcs.com/wp-admin/admin.php?page=wp2pcs_service&tab=sites" target="_blank" class="button">获取外链码</a>
<input type="hidden" name="action" value="update-outlink-setting">
<?php wp_nonce_field(); ?>

</form>
</div></div><!-- // -->

</div>