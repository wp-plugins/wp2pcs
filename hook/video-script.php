<?php

// 在网页头部增加样式
add_action('wp_head','wp2pcs_video_player_style');
function wp2pcs_video_player_style() {
  //if(!get_option('wp2pcs_site_id') || !get_option('wp2pcs_video_m3u8')) return;
  echo '<style>';
  echo 'iframe.wp2pcs-video-player{display:block;margin:1em auto;background:url('.plugins_url('assets/video-play.png',WP2PCS_PLUGIN_NAME).') no-repeat center #f5f5f5;border:0;}';
  echo 'iframe.wp2pcs-video-playing{display:block;margin:1em auto;background:url('.plugins_url('assets/loading.gif',WP2PCS_PLUGIN_NAME).') no-repeat center #f5f5f5;border:0;}';
  echo '</style>';
}

// 在网页底部增加脚本
add_action('wp_footer','wp2pcs_video_player_script');
function wp2pcs_video_player_script() {
  $site_id = get_option('wp2pcs_site_id');
  if(!$site_id || !get_option('wp2pcs_video_m3u8')) return;
  echo '<script>window.jQuery || document.write(\'<script type="text/javascript" src="'.plugins_url("assets/jquery-1.11.2.min.js",WP2PCS_PLUGIN_NAME).'">\x3C/script>\');</script>';
  echo '<script type="text/javascript">';
  echo 'function wp2pcs_setup_videos() {';
  echo 'jQuery("iframe.wp2pcs-video-player").each(function(){';
  echo 'var $this = jQuery(this),';
      echo 'path = $this.attr("data-path"),';
      echo 'width = $this.attr("width"),';
      echo 'height = $this.attr("height"),';
      echo 'stretch = $this.attr("data-stretch"),';
      echo 'autostart = $this.attr("data-autostart"),';
      echo 'md5 = $this.attr("data-md5"),';
      echo 'root_dir = $this.attr("data-root-dir"),';
      echo 'image = $this.attr("data-image");';
  echo 'if(root_dir != undefined) {';
      echo 'if(root_dir == "share") root_dir = "/apps/wp2pcs/share";';
  echo '}';
  echo 'else {';
      echo 'root_dir = "'.WP2PCS_BAIDUPCS_REMOTE_ROOT.'/load";';
  echo '}';
  echo 'if(path.indexOf(root_dir) != 0) path = root_dir + path;';
  echo 'path = path.replace("&","%26");';
  echo 'path = path.replace("\'","%27");';
  echo 'path = path.replace("\"","%22");';
  echo '$this.attr("src","'.WP2PCS_APP_URL.'/video?site_id='.$site_id.'&size=" + width + "_" + height + "&stretch=" + stretch + "&autostart=" + autostart + "&image=" + image + "&path=" + path);';
  echo '$this.removeClass("wp2pcs-video-player").addClass("wp2pcs-video-playing");';
  echo '$this.attr("frameborder","0");';
  echo '$this.attr("scrolling","no");';
  echo '});';
  echo '}';
  echo 'wp2pcs_setup_videos();';// 如果某些网站采用了ajax加载页面，可以在ajax加载完之后执行一次wp2pcs_setup_videos();，从而可以让视频加载。
  echo '</script>';
}