(function($) {

$.widget('ui.sympalSlotEditor', {
  
  options: {
    slotType:   null
  },
  
  _create: function() {
    this.initialize();
  },
  
  initialize: function() {
    var self = this;
    
    // register the ajax form submit event
    this._ajaxForm();
    
    // hook up the cancel button
    $('input.cancel', this.getForm()).click(function() {
      // trigger a close event
      
      self.element.trigger('close');
      return false;
    });

    // register the type-change toggle link
    $('.sympal_change_slot_type', this.getForm()).click(function() {
      $('.sympal_change_slot_type_dropdown', self.getForm()).slideToggle();
    });

    // register the type drop-down change event
    $('.sympal_change_slot_type_dropdown', self.getForm()).change(function() {
      
      // the url is matadata on the dropdown wrapper
      url = $(this).metadata().url.replace('/replace', '/' + $('select', this).val());
      console.log(url);
      
      $.blockUI();
      $.post(url, {}, function(data) {
        
        // push the content into .form_body
        $('.form_body', self.getForm()).html(data);
        
        // trigger ajaxResponseSuccess to setup everything again
        self.getForm().trigger('ajaxResponseSuccess');
        
        // unblock the ui
        $.unblockUI();
      });
    });
    
    // register the ajax response event
    this._bindAjaxResponseEvent();
    
    this.getForm().trigger('ajaxResponseSuccess');
  },
  
  _ajaxForm: function() {
    // Attach the form ajax submit event to the form
    
    var self = this;
    var form = this.getForm();
    
    form.submit(function() {
      self.block();
      
      // trigger the event, allow anybody to prep anything
      $(this).trigger('preFormSubmit');
      
      $(this).ajaxSubmit( {
        error: function(xhr, textStatus, errorThrown) {
          self.unblock();
          // display some sort of error
        },
        success: function(responseText, statusText, xhr) {
          $('.form_body', form).html(responseText);
          form.trigger('ajaxResponseSuccess');
          self.unblock();
        }
      });
      
      return false;
    });
  },
  
  _bindAjaxResponseEvent: function() {
    // Creates an ajaxResponseSuccess, which should be triggered whenever
    // the contents of the form (.form_body) are ajaxed
    
    var self = this;
    var form = this.getForm();
    
    // register the ajaxSuccess function on this editor
    form.bind('ajaxResponseSuccess', function() {
      self.block();
      
      // initialize any slot-specific functionality if it exists
      var formClass = 'sfSympalSlot'+self.option('slotType');
      if ($.isFunction(self.element[formClass]))
      {
        self.element[formClass](self);
      }
      
      // Keep track of the currently focused editor
      $('input:text, textarea', form).focus(function() {
        currentlyFocusedSympalEditor = $(this);
      });
      
      self.unblock();
    }); // end ajaxResponseSuccess
  },
  
  block: function() {
    // If we're not working on a block element, we've gotta block the whole page
    if (this.isBlock())
    {
      // you actually want to block the parent, (i.e. #facebox-wrapper)
      this.element.parent().block();
    }
    else
    {
      $.blockUI();
    }
  },
  
  unblock: function() {
    if (this.isBlock())
    {
      this.element.parent().unblock();
    }
    else
    {
      $.unblockUI();
    }
  },
  
  isBlock: function() {
    return (this.element.css('display') == 'block');
  },
  
  getForm: function() {
    if (!this.option('form'))
    {
      this._setOption('form', $('form', this.element));
    }
    
    return this.option('form');
  },
  
  destroy: function() {
    // unbind all the close events
    this.getForm().unbind('close');
    
    // destroy this widget
    $.Widget.prototype.destroy.apply(this, arguments);
  },
});


})(jQuery);