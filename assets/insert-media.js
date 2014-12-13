jQuery(function($){
  // 点击帮助按钮
  $('#wp2pcs-insert-media-btn-help').on('click',function(){
    $('#wp2pcs-insert-media-iframe-content').toggle();
    $('#wp2pcs-insert-media-iframe-help').fadeToggle();
  });
  // 点击文件区域
  $('#wp2pcs-insert-media-iframe-files').on('click','.file-on-pcs:not(.file-type-dir)',function(e){
    if($(e.target).prop('tagName') == 'INPUT') return;
    var $this = $(this),
        $input = $this.children('input');
    $this.toggleClass('selected');
    if($this.hasClass('selected')) {
      $input.prop('checked',true);
    }
    else {
      $input.prop('checked',false);
    }
  });
  // 变化勾选状况
  $('#wp2pcs-insert-media-iframe-files').on('change','.file-on-pcs input',function(){
    var $this = $(this),
        $box = $this.parent();
    if($this.prop('checked') == true) {
      $box.addClass('selected');
    }
    else {
      $box.removeClass('selected');
    }
  });
  // 清除选择的图片
  $('#wp2pcs-insert-media-btn-clear').click(function(){
    $('.file-on-pcs').removeClass('selected');
    $('.file-on-pcs input').prop('checked',false);
  });
  // 点击插入按钮
  $('#wp2pcs-insert-media-btn-insert').click(function(){
    if($('.file-on-pcs.selected').length > 0) {
      var html = '';
      $('.file-on-pcs.selected').each(function(){
        var $this = $(this),
            $input = $this.children('input'),
            is_img = $input.attr('data-img'),
            is_link = $input.attr('data-link'),
            is_video = $input.attr('data-video'),
            is_play = $input.attr('data-play'),
            video_path = $input.attr('data-file-path'),
            video_md5 = $input.attr('data-file-md5'),
            is_music = $input.attr('data-music'),
            url = $input.val();
        // 如果被选择的是图片
        if(is_img == 1){
          if(is_link == 1) html += '<a href="' + url + '">';
          html += '<img src="' + url + '" class="wp2pcs-img">';
          if(is_link == 1) html += '</a>';
        }
        // 如果是视频
        else if(is_video == 1) {
          if(is_play == 1) {
            html += '<p><div class="wp2pcs-video-player" data-path="' + video_path + '" data-md5="' + video_md5 + '"><a href="' + url + '">&nbsp;</a></div></p>';
          }
          else {
            html += '<p>' + url + '</p>';
          }
        }
        else if(is_music == 1) {
          html += '<p>' + url + '</p>';
        }
        // 如果是其他文件，就直接给媒体链接
        else{
          html += url;
        }
        html += "\r\n\r\n";
      });
      $('#wp2pcs-insert-media-btn-clear').click();
      // http://stackoverflow.com/questions/13680660/insert-content-to-wordpress-post-editor
      window.parent.send_to_editor(html);
      window.parent.tb_remove();
    }else{
      alert('没有选择任何附件');
    }
  });
  // 下拉加载
  $(window).scroll(function(){
    var $window = $(window),
        scroll_top = $window.scrollTop(),
        screen_height = $window.height(),
        $pagenavi = $('#wp2pcs-insert-media-iframe-pagenavi'),
        $next = $pagenavi.find('a.next-page'),
        href = $next.attr('href'),
        loading = $pagenavi.attr('data-loading'),
        ajaxing = $pagenavi.attr('data-ajaxing');
    if($pagenavi.length > 0 && scroll_top + screen_height + 100 > $pagenavi.offset().top && href != undefined) {
    
    if(ajaxing == 'true') return;
    $pagenavi.attr('data-ajaxing','true');
    $.ajax({
      url : href,
      dataType : 'html',
      type : 'GET',
      timeout : 10000,
      beforeSend : function() {
        $pagenavi.html('<img src="' + loading + '">');
      },
      success : function(data) {
        var DATA = $(data),
            DATA = $('<code></code>').append(DATA),
            LIST = $('#wp2pcs-insert-media-iframe-files',DATA),
            NAVI = $('#wp2pcs-insert-media-iframe-pagenavi',DATA);
        $('#wp2pcs-insert-media-iframe-files').append(LIST.html());
        if(NAVI.find('a.next-page').length > 0) {
          $pagenavi.html(NAVI.html()).removeAttr('data-ajaxing');
        }
        else {
          $pagenavi.remove();
        }
      },
      error : function() {
        $pagenavi.html('<a href="' + href + '">下一页</a>').removeAttr('data-ajaxing');
      }
    });
    
    } // -- endif --
  });
  // 刷新按钮
  $('#wp2pcs-insert-media-btn-refresh').click(function(e){
    e.preventDefault();
    var $this = $(this),
        $body = $('#wp2pcs-insert-media-iframe-content'),
        href = $this.attr('href'),
        loading = $this.attr('data-loading'),
        ajaxing = $this.attr('data-ajaxing');
    if(ajaxing == 'true') return;
    $this.attr('data-ajaxing','true');
    $.ajax({
      url : href,
      dataType : 'html',
      type : 'GET',
      timeout : 10000,
      beforeSend : function() {
        $body.html('<img src="' + loading + '" style="display:block;margin: 0 auto;margin-top: 10%;">');
      },
      success : function(data) {
        var DATA = $(data),
            DATA = $('<code></code>').append(DATA),
            CONTENT = $('#wp2pcs-insert-media-iframe-content',DATA);
        $body.html(CONTENT.html());
        $this.removeAttr('data-ajaxing');
      },
      error : function() {
        $this.removeAttr('data-ajaxing');
      }
    });
  });

});
