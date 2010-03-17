(function($) {

$.fn.extend({
  
  sympalDroppableInput: function(callback)
  {
    return this.each(function()
    {
      var $input = $(this);

      if (!$input.hasClass('ui-droppable'))
      {
        accept = '#sympal_assets_list li a.insert';

        $input.droppable({
          accept: accept,
          activeClass: 'droppable_active',
          hoverClass: 'droppable_hover',
          drop: function(e, ui)
          {
            callback && $.isFunction(callback) && callback.apply($input);
          }
        });
      }
    });
  },
  
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