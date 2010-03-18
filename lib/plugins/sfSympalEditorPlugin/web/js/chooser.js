/**
 * jQuery widget that representing a widget for the "choosing" drawer
 * from which you can select assets, pages, etc
 */

(function($) {

$.widget('ui.sympalChooser', {
  
  _init : function()
  {
    this.initialize();
  },
  
  openDrawer : function()
  {
    url = this.element.attr('href');
    
    $.openChooserDrawer(url, this.options.chooserCallback);
    
      //._setData('callback', self._getData('callback'))
    //  .open(url);
  },
  
  closeDrawer : function()
  {
    $.closeChooserDrawer();
  },
  
  initialize : function()
  {
    self = this;
    
    // open drawer on click
    this.element.click(function() {
      self.openDrawer();
      
      return false;
    });
  },

});

$.extend($.ui.sympalChooser, {
  defaults:
  {
    chooserCallback : function() {
      alert('put default callback (insert into editor) here');
    }
  }
});


/**
 * Represents the actual choosable drawer
 */
$.extend({
    
  openChooserDrawer : function(url, callback)
  {
    var drawer = $('#sympal_chooser_container')
    
    drawer.slideDown('fast');
    drawer.load(url, function() {
      // make any .insert elements respond to the given callback
      $('.insert', drawer).click(function(){
        callback && $.isFunction(callback) && callback.apply($(this).attr('alt'));
      });
      
      // Close all dropdown menus when class="sympal_close_menu" is clicked
      $('.sympal_close_menu', drawer).click(function() {
        $.closeChooserDrawer();
        
        return false;
      });
    });
  },
  
  closeChooserDrawer : function()
  {
    var drawer = $('#sympal_chooser_container')
    
    drawer.slideUp('fast');
    drawer.html('')
  }
  
});

})(jQuery);