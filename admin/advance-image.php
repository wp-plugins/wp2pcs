<div class="wrap">

<h2 class="nav-tab-wrapper">
  <a href="<?php menu_page_url('wp2pcs-advance'); ?>" class="nav-tab">站点</a>
  <a href="<?php echo add_query_arg('tab','outlink',menu_page_url('wp2pcs-advance',false)); ?>" class="nav-tab">外链</a>
  <a href="<?php echo add_query_arg('tab','video',menu_page_url('wp2pcs-advance',false)); ?>" class="nav-tab">视频</a>
  <a href="javascript:void(0)" class="nav-tab nav-tab-active">图片</a>
</h2>

<div class="metabox-holder"><div class="meta-box-sortables">
<form method="post" autocomplete="off">

<?php
include('tpl/advance-setup.php');
include('tpl/advance-site-info.php');
include('tpl/set-linktype.php');
?>

<?php
$wp2pcs_image_watermark = get_option('wp2pcs_image_watermark');
?>
<div class="postbox">
  <div class="handlediv" title="点击以切换"><br></div>
  <h3 class="hndle">图片水印</h3>
  <div class="inside">
    <p>水印图片的路径：<?php echo realpath(ABSPATH); ?><input type="text" name="wp2pcs_image_watermark" value="<?php echo str_replace(realpath(ABSPATH),'',$wp2pcs_image_watermark); ?>" class="regular-text"></p>
    <p>请先<a href="<?php echo admin_url('media-new.php'); ?>" target="_blank">上传</a>一张用来作为水印的图片，然后把图片的相对路径填写在这里（网站的根路径已经给出），注意填写的路径以/或\开头。</p>
    <p><small>注意：1.仅在选择第一种或第二种附件格式时有效；2.你的主机必须支持GD库；3.使用该功能会占用更多主机资源；4.仅支持jpg,jpeg,png,gif,bmp这几种格式；5.默认水印在右下角，50%透明度，请使用尺寸较小的水印图片。</small></p>
  </div>
</div>

<p><button type="submit" class="button-primary">确定</button></p>
<input type="hidden" name="action" value="update-image-setting">
<?php wp_nonce_field(); ?>
</form>
</div></div><!-- // -->

</div>