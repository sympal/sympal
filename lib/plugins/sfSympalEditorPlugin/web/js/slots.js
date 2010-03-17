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
    self = this;
    
    // determine the editor object
    if (this._getData('edit_mode') == 'in-place')
    {
      var editor = $('.sympal_slot_content', self.element);
    }
    else
    {
      var editor = $('#fancybox-inner');
    }
    
    // register the ajaxSuccess function on the editor
    editor.bind('ajaxResponseSuccess', function() {
      $form = $('form', editor);
      
      // setup the ajax form submit
      $form.submit(function() {
        editor.trigger('block');
        
        // before submit
        //sympalTinyMCETriggerSave();
        
        $form.ajaxSubmit( {
          error: function(xhr, textStatus, errorThrown)
          {
            editor.trigger('unblock');
            // display some sort of error
          },
          success: function(responseText, statusText, xhr)
          {
            $('form_body', $form).html(responseText);
            editor.trigger('ajaxResponseSuccess');
            editor.trigger('unblock');
          }
        });
        
        return false;
      });
      
      // hook up the cancel button
      $('form input.cancel', this.element).click(function() {
        editor.trigger('close');
        
        return false;
      });
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
    href = $('.sympal_slot_button', this.element).attr('href');
    if (this._getData('edit_mode') == 'in-place')
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
      if (editor.attr('id') == 'fancybox-inner')
      {
        $.fancybox.close();
      }
      else
      {
        editor.html('');
      }
      
      $.blockUI();
      
      $('.sympal_slot_content', self.element).load(
        self._getData('view_url'),
        function() {
          $.unblockUI();
        }
      );
    });
  },
  
  closeEditor : function()
  {
    if ($('form', this.element).eq(0).hasClass('popup'))
    {
      // popup closing
      $.fancybox.close();
    }
    else
    {
      // inline closing
      //sympal_toggle_inline_edit_slot(wrapperEle, false);
    }
  },
  
  initialize: function()
  {
    var self = this;
    
    // open up the editor
    $('a.sympal_slot_button', this.element).click(function() {
      self.openEditor()
      
      return false;
    });
    
    // show the edit button when hovering over editable area
    this.element.hover(function() {
      $('.sympal_slot_button:not(.no-hover)', self.element).show();
    }, function() {
      $('.sympal_slot_button:not(.no-hover)', self.element).hide();
    });
    
    $('.sympal_slot_content').bind('dblclick', function() {
      $('.sympal_slot_button', self.element).click();
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
  
  // globally hide slot formss on the "cancel" edit button
  $('#inline-edit-bar-buttons-menu .sympal_disable_edit_mode').click(function(){
    $('form.sympal_slot_form input.cancel').click();
  });
});