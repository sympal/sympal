/**
 * jQuery widget that representing a widget for the "choosing" drawer
 * from which you can select assets, pages, etc
 */

(function($) {

$.widget('ui.sympalChooser', {

  options : {
    
    callback : function() {
      alert('default callback');
    }
  },
  
  _init : function()
  {
    this.initialize();
  },
  
  openDrawer : function()
  {
    url = this.element.attr('href');
    
    // make sure the 
    var drawer = this.getDrawer().sympalChooserDrawer();
    drawer.sympalChooserDrawer('open', url);
    
      //._setData('callback', self._getData('callback'))
    //  .open(url);
  },
  
  closeDrawer : function()
  {
    this.getDrawer().close();
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
  
  getDrawer : function() {
    return drawer = $('#sympal_chooser_container');
  }

});


/**
 * Represents the actual choosable drawer
 */
$.widget('ui.sympalChooserDrawer', {
  
  options : {
    
    callback : function() {
      alert('default callback');
    }
    
  },
  
  _init : function()
  {
    self = this;
    
    this.element.bind('ajaxResponseSuccess', function ()
    {
      // make any .sympal_choosable elements respond to the given callback
      $('.sympal_choosable', self.element).click(function(){
        callback = self._getData('callback');
        
        callback && $.isFunction(callback) && callback.apply($(this).attr('alt'));
      });
      
      // Close all dropdown menus when class="sympal_close_menu" is clicked
      $('.sympal_close_menu', self.element).click(function() {
        self.close();
        
        return false;
      });
      
    });
  },
  
  open : function(url)
  {
    self = this;
    
    self.element.slideDown('fast');
    this.element.load(url, function() {
      self.trigger('ajaxResponseSuccess');
    });
  },
  
  close : function()
  {
    this.element.slideUp('fast');
  }
  
});

})(jQuery);

$(document).ready(function(){
  $('.toggle_sympal_assets').sympalChooser();
});