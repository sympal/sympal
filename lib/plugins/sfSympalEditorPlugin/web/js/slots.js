(function($) {

$.widget('ui.sympalSlot', {
  
  options : {
    edit_mode : 'popup'
  },
  
  _init : function()
  {
    if (edit_mode = this.element.metadata().edit_mode)
    {
      this._setData('edit_mode', edit_mode);
    }
    
    if (slot_type = this.element.metadata().slot_type)
    {
      this._setData('slot_type', slot_type);
    }

    if (view_url = this.element.metadata().view_url)
    {
      this._setData('view_url', view_url);
    }
    
    this.initialize();
  },
  
  openEditor : function()
  {
    var self = this;
    
    // disable the non-edit-mode controls
    this.disableNonEditControls();
    
    // make sure the edit button is hidden
    $('.sympal_slot_button', this.element).hide();
    
    // determine the editor object
    if (this._getData('edit_mode') == 'inline')
    {
      var editor = $('.sympal_slot_content', self.element);
    }
    else
    {
      var editor = $('#fancybox-inner');
    }
    this._setData('editor', editor);
    
    // register the ajaxSuccess function on the editor
    editor.bind('ajaxResponseSuccess', function() {
      editor.trigger('block');
      
      // setup the ajax form submit
      $('form:not(.sympal_ajax_submit)', editor).submit(function() {
        editor.trigger('block');
        
        // make sure the event doesn't get re-registered
        $(this).addClass('sympal_ajax_submit');
        
        $(this).ajaxSubmit( {
          error: function(xhr, textStatus, errorThrown)
          {
            editor.trigger('unblock');
            // display some sort of error
          },
          success: function(responseText, statusText, xhr)
          {
            $('.form_body', editor).html(responseText);
            editor.trigger('ajaxResponseSuccess');
            editor.trigger('unblock');
          }
        });
        
        return false;
      }); // end ajax for submit
      
      // initialize any slot-specific functionality if it exists
      var formClass = 'sfSympalSlot'+self.element.metadata().type;
      if ($.isFunction(self.element[formClass]))
      {
        self.element[formClass](editor);
      }
      
      // Keep track of the currently focused editor
      $('input:text, textarea', $(this)).focus(function() {
        currentlyFocusedSympalEditor = $(this);
      });
      
      // hook up the cancel button
      $('form input.cancel', this).click(function() {
        editor.trigger('close');
        
        return false;
      });
      
      editor.trigger('unblock');
    }); // end ajaxResponseSuccess
    
    // bind the block and unblocks
    editor.bind('block', function() {      
      // inline, we've gotta block the whole page
      if ($(this).css('display') == 'block')
      {
        // you actually want to block the parent, #facebox-wrapper
        $(this).parent().block();
      }
      else
      {
        $.blockUI();
      }
    });
    editor.bind('unblock', function() {
      if ($(this).css('display') == 'block')
      {
        $(this).parent().unblock();
      }
      else
      {
        $.unblockUI();
      }
    });
    
    // actually ajax in the data and trigger editor.ajaxResponseSuccess
    var href = $('.sympal_slot_button', this.element).attr('href');
    if (this._getData('edit_mode') == 'inline')
    {
      editor.load(href, function() {
        editor.trigger('ajaxResponseSuccess');
      });
    }
    else
    {
      $.fancybox(href, {
        'zoomSpeedIn': 300,
        'zoomSpeedOut': 300,
        'overlayShow': true,
        'height': 440,
        'autoDimensions': false,
        'hideOnContentClick': false,
        'type': 'ajax',
        'onComplete': function() {
          editor.trigger('ajaxResponseSuccess');
        },
        'onCleanup': function() {
          editor.trigger('close')
        },
      });
    }
    
    editor.bind('close', function() {
      self.closeEditor();
    });
  },
  
  closeEditor : function()
  {
    self = this;
    var editor = this._getData('editor');
    
    $.blockUI();
    
    // ajax in the content, and then set things back up
    $('.sympal_slot_content', self.element).load(
      self._getData('view_url'),
      function() {
        // reinitialize the non-edit-mode controls
        self.enableNonEditControls();
        
        if (editor.attr('id') == 'fancybox-inner')
        {
          $.fancybox.close();
        }
        else
        {
          // nothing needs to be done inline, the ajax just repalced the content
        }
        
        $.unblockUI();
      }
    );
  },
  
  initialize: function()
  {
    var self = this;
    
    // register non-edit-handlers: effects for when the slot is not being edited
    nonEditHandlers = {};
    
    // enable editing on double-click
    nonEditHandlers['dblclick'] = function()
    {
      self.openEditor()
    }
    nonEditHandlers['mouseover'] = function()
    {
      $('.sympal_slot_button', self.element).show();
    }
    nonEditHandlers['mouseout'] = function()
    {
      $('.sympal_slot_button', self.element).hide();
    }
    this._setData('nonEditHandlers', nonEditHandlers);
    
    // attach the nonEditHandler events
    this.enableNonEditControls();
    
    // enable editing if the slot button is clicked
    $('a.sympal_slot_button', this.element).click(function() {
      self.openEditor()
      return false;
    });
    
    // highlight editable area on edit button hover
    $('a.sympal_slot_button', this.element).hover(function() {
      $('.sympal_slot_content', self.element)
        .css('opacity', .2)
        .children().css('opacity', .2);
    }, function() {
      $('.sympal_slot_content', self.element)
        .css('opacity', 1)
        .children().css('opacity', 1);
    });
  },
  
  enableNonEditControls : function()
  {
    self = this;
    
    // bind all of the non-edit-mode handlers
    $.each(this._getData('nonEditHandlers'), function(key, value) {
      self.element.bind(key, value);
    });
  },
  
  disableNonEditControls : function()
  {
    self = this;
    
    // disable all of the non-edit-mode handlers
    $.each(this._getData('nonEditHandlers'), function(key, value) {
      self.element.unbind(key, value);
    });
  }
  
});

})(jQuery);



jQuery(document).ready(function(){    
  
  $('.sympal_slot_wrapper').sympalSlot();
  
  $('.sympal_inline_edit_bar_edit_buttons').show();
  
  // globally save slots on the "save" edit button
  $('#inline-edit-bar-buttons-menu .sympal_save_content_slots').click(function(){
    $('form.sympal_slot_form').submit();
  });
  
  // globally hide slot forms on the "cancel" edit button
  $('#inline-edit-bar-buttons-menu .sympal_disable_edit_mode').click(function(){
    $('form.sympal_slot_form input.cancel').click();
  });
});