<div class="postbox">
  <div class="handlediv" title="点击以切换"><br></div>
  <h3 class="hndle">你的信息</h3>
  <div class="inside">
    <p>当前站点：<input type="text" value="<?php echo substr(home_url(),strpos(home_url(),'://')+3); ?>" class="regular-text" readonly></p>
    <p>
      站点码：<input type="password" value="<?php echo $wp2pcs_site_code; ?>" disabled>
      <a href="http://www.wp2pcs.com/wp-admin/admin.php?page=work-manager-service" target="_blank" class="button">获取站点码</a>
    </p>
    <?php if($wp2pcs_site_id) { ?>
    <p>
      站点号：<?php echo $wp2pcs_site_id; ?>
      到期时间：<?php echo date('Y-m-d',$wp2pcs_site_expire); ?>
      <strong><?php if(time() > $wp2pcs_site_expire) echo '已到期';else echo '已成功开启'; ?></strong>
    </p>
    <?php }else{ ?>
    <p>当前尚未通过站点验证。点击上方“站点”选项验证站点。</p>
    <?php } ?>
  </div>
</div>
