/**
 * @file
 * Global JS file for announcement modal.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.announcement = {
    attach: function (context, settings) {
      // Check if announcement drupal settings available.'
      if(typeof drupalSettings.announcement === 'undefined'){
        return;
      }
      Drupal.behaviors.announcement.announcementModal(context);

    },
    /**
     *  @TODO
     */
    announcementModal: function(context){
      var target = $('#announcement-banner', context);
      var widget = $('.stickey-widget', context);
      var imgurl = drupalSettings.announcement.image_url;
      var imgtitle = drupalSettings.announcement.image_title;
      var imgdesc = drupalSettings.announcement.image_desc;
      var imgbg = drupalSettings.announcement.image_bg;
      var modalContainer = $('<div/>', {
        class: 'modal fade',
        id: 'announcement',
        role: 'dialog'
      });
      var modalDialog = $('<div class="modal-dialog"/>'),
        modalContent = $('<div class="modal-content"/>'),
        modalBody = $('<div class="modal-body"/>'),
        modalButton = $('<a></a>', {role: 'button', 'data-dismiss': 'modal', class: 'close'});
      modalBody.append(modalButton);
      modalBody.append($('<img/>', {src: imgurl, class: 'img-responsive modal-image'}));
      modalBody.append($('<div class="content-wrapper"><h1 class="img-title">'+imgtitle+'</h1><div class="img-desc">'+imgdesc+'</div></div>'));
      modalContent.appendTo(modalDialog);
      modalBody.appendTo(modalContent);
      modalDialog.appendTo(modalContainer);
      modalContainer.appendTo(target);
      if (imgbg) {
        target.find('.modal-dialog').addClass('modal-bg');
        target.find('.modal-body').addClass(imgbg);
      }
      // Adding style on scroll to bottom.
      $(window).scroll(function () {
        var scroll = $(window).scrollTop();
        var height = $(window).height();
        if(scroll + height > $(document).height() - 100) {
          widget.addClass('stickey-hide');
        }else{
          widget.removeClass('stickey-hide');
        }
      });
      // check cookie
      var visited = $.cookie("visited");
      if (visited == null) {
        $(window,context).once().on('load', function(){
          modalContainer.modal('show');
          $.cookie('visited', 'yes', { expires: 1 });
        });
      }
      else {
        widget.addClass('stickey-show');
      }
      // Toggle widget link.
      modalContainer.on('hide.bs.modal', function () {
        widget.addClass('stickey-slide');
        widget.removeClass('stickey-show');
      });
      $(widget).find('a').on('click', function () {
        widget.removeClass('stickey-slide');
        widget.removeClass('stickey-show');
      });

    }
  };
})(jQuery, Drupal, drupalSettings);
