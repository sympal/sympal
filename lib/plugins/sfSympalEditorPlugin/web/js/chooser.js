/**
 * jQuery widget that representing a widget for the "choosing" drawer
 * from which you can select assets, pages, etc
 */

(function($) {

$.widget('ui.sympalChooser', {
  
  _init: function() {
    this.initialize();
  },
  
  openDrawer: function() {
    var url = this.element.attr('href');
    
    $.openChooserDrawer(url, this.options.chooserCallback);
  },
  
  closeDrawer: function() {
    $.closeChooserDrawer();
  },
  
  initialize: function() {
    var self = this;
    
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
    chooserCallback: function(val, options) {
      alert('You chose ' + val);
    }
  }
});


/**
 * Represents the actual choosable drawer
 */
$.extend({
    
  openChooserDrawer: function(url, callback) {
    var drawer = $('#sympal_chooser_container')
    
    drawer.slideDown('fast');
    drawer.load(url, function() {
      // make any .insert elements respond to the given callback
      $('.insert', drawer).click(function(){
        if (callback && $.isFunction(callback))
        {
          var chosenValue = $(this).attr('title') + '';
          var chosenOptions = $(this).metadata();
          
          callback(chosenValue, chosenOptions);
        }
        
        return false;
      });
      
      // Close all dropdown menus when class="sympal_close_menu" is clicked
      $('.sympal_close_menu', drawer).click(function() {
        $.closeChooserDrawer();
        
        return false;
      });
    });
  },
  
  closeChooserDrawer: function() {
    var drawer = $('#sympal_chooser_container')
    
    drawer.slideUp('fast');
    drawer.html('')
  }
  
});

})(jQuery);