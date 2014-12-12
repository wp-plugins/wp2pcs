<?php

add_action('wp_footer','wp2pcs_video_player_script');
function wp2pcs_video_player_script() {
?>
<script>
if(window.jQuery) {jQuery(function($){
  $(document).on('click','.wp2pcs-video-player',function(e){
    e.preventDefault();
    var $this = $(this),
        path = $this.attr('data-path'),
        md5 = $this.attr('data-md5');
    $this.html('<iframe src="http://pan.baidu.com/res/static/thirdparty/guanjia/guanjia_play.html?path=' + path + '&md5=' + md5 + '" style="display:block;width:100%;height:100%;" frameborder="0" framescroll="none" class="wp2pcs-video-iframe"></iframe>');
  });
})}
</script>
<?php
}
