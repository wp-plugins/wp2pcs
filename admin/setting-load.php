<div class="wrap">

<h2 class="nav-tab-wrapper">
  <a href="<?php menu_page_url('wp2pcs-setting'); ?>" class="nav-tab">基本信息</a>
  <a href="javascript:void(0)" class="nav-tab nav-tab-active">资源调用</a>
  <a href="<?php echo add_query_arg('tab','backup',menu_page_url('wp2pcs-setting',false)); ?>" class="nav-tab">定时备份</a>
</h2>

<div class="metabox-holder"><div class="meta-box-sortables">
<form method="post" autocomplete="off">

<?php if(!BAIDUPCS_ACCESS_TOKEN) { ?>
<div class="error"><p><strong>提示</strong>：还没有百度授权。</p></div>
<?php } ?>

<div class="postbox">
  <div class="handlediv" title="点击以切换"><br></div>
  <h3 class="hndle">云端路径</h3>
  <div class="inside">
    <p>所有资源请放在百度网盘<code><a href="http://pan.baidu.com/disk/home#dir/path=<?php echo urlencode(BAIDUPCS_REMOTE_ROOT.'/load'); ?>" target="_blank"><?php echo BAIDUPCS_REMOTE_ROOT; ?>/load</a></code>目录中</p>
  </div>
</div>

<?php include('tpl/set-linktype.php'); ?>

<?php $wp2pcs_load_imglink = (int)get_option('wp2pcs_load_imglink'); ?>
<div class="postbox">
  <div class="handlediv" title="点击以切换"><br></div>
  <h3 class="hndle">媒体插入</h3>
  <div class="inside">
    <p>插入图片时插入其链接？<select name="wp2pcs_load_imglink">
      <option value="0" <?php selected($wp2pcs_load_imglink,0); ?>>关闭</option>
      <option value="1" <?php selected($wp2pcs_load_imglink,1); ?>>开启</option>
    </select></p>
  </div>
</div>

<?php $wp2pcs_load_cache = (int)get_option('wp2pcs_load_cache');  ?>
<div class="postbox">
  <div class="handlediv" title="点击以切换"><br></div>
  <h3 class="hndle">本地缓存</h3>
  <div class="inside">
    <p><select name="wp2pcs_load_cache">
      <option value="0" <?php selected($wp2pcs_load_cache,0); ?>>关闭</option>
      <option value="1" <?php selected($wp2pcs_load_cache,1); ?>>开启</option>
    </select> 一个文件再被访问<?php echo WP2PCS_CACHE_COUNT; ?>次后会被缓存在本地。媒体列表缓存在本地。</p>
    <p><a href="<?php echo add_query_arg(array('action'=>'clean-cache','_wpnonce'=>wp_create_nonce())); ?>" class="button">清空所有缓存</a></p>
  </div>
</div>

<button type="submit" class="button-primary">确定</button>
<input type="hidden" name="action" value="update-load-setting">
<?php wp_nonce_field(); ?>

</form>
</div></div><!-- // -->

</div>
