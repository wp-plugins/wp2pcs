<div class="wrap">

<h2 class="nav-tab-wrapper">
  <a href="<?php menu_page_url('wp2pcs-advance'); ?>" class="nav-tab">站点</a>
  <a href="javascript:void(0)" class="nav-tab nav-tab-active">外链</a>
  <a href="<?php echo add_query_arg('tab','video',menu_page_url('wp2pcs-advance',false)); ?>" class="nav-tab">视频</a>
</h2>

<div class="metabox-holder"><div class="meta-box-sortables">
<form method="post" autocomplete="off">

<?php
include('tpl/advance-setup.php');
include('tpl/advance-site-info.php');
include('tpl/set-linktype.php');
?>

<p><button type="submit" class="button-primary">确定</button></p>
<input type="hidden" name="action" value="update-outlink-setting">
<?php wp_nonce_field(); ?>
</form>
</div></div><!-- // -->

</div>
