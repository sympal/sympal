(function($) {

$.sympal = {
  
};

$.fn.extend({
  
});

$.extend({

  showFlashMessage : function (type, message)
  {
    var container = $('#sympal_flash_internal');
    var isVisible = container.parent().is(':visible');
    
    container.html('<div class="message '+type+'">'+message+'</div>');
    container.append('<a href="#" onclick="$(this).parent().parent().stop().slideUp(); return false;" class="hide">X</a>');
    
    // make the container slide down and then back up. If another flash
    // is thrown, just have it restart the timer
    container.parent()
      .stop(true, false)
      .slideDown()
      .animate({ delay: 1 }, 5000, function() {
        container.parent().slideUp();
      });
  }
  
});

// jQuery plugins
if ($.blockUI)
{
 $.blockUI.defaults = $.extend($.blockUI.defaults, {
    css:        {},
    overlayCSS: {},
    message:    ' ',
  fadeIn:     0,
  fadeOut:    0
 });
}

})(jQuery);