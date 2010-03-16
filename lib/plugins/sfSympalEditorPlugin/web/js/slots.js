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
    
    this.initialize();
  },
  
  openEditor : function()
  {
    self = this;
    // 1) actually initialize one of the two editors
    // 2) add buttons
    // 3) setup ajax form save
    
    if (this._getData('edit_mode') == 'in-place')
    {
      var editor = $('.sympal_slot_content', self);
    }
    else
    {
      var editor = $('#fancybox-inner');
    }
    
    // register the ajaxSuccess function
    editor.bind('ajaxResponseSuccess', function() {
      $form = $('form', editor);
      
      // setup the ajax form submit
      $form.submit(function() {
        editor.block({
          message: '<h4>Saving...</h4>'
        });
        
        // before submit
        //sympalTinyMCETriggerSave();
        //formEle.find('img.loading_anim').show();
        //$('#sympal_slot_flash').slideUp();
        
        $form.ajaxSubmit( {
          error: function(xhr, textStatus, errorThrown)
          {
            editor.unblock();
            // display some sort of error
          },
          success: function(responseText, statusText, xhr)
          {
            $('form_body', $form).html(responseText);
            editor.trigger('ajaxResponseSuccess');
            editor.unblock();
          }
        });
        
        return false;
      });
    });
    
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
        }
      });
    }
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
    
    /*
     * put in editor initialize
    // the cancel button for the editor
    $('form input.cancel', this.element).click(function() {
      self.closeEditor();
      
      return false;
    });
    */
    
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
  
  // globally save slots on the "save" edit button
  $('#inline-edit-bar-buttons-menu .sympal_save_content_slots').click(function(){
    $('.sympal_slot_form.edit_enabled form').submit();
  });
  
  // globally hide slot formss on the "cancel" edit button
  $('#inline-edit-bar-buttons-menu .sympal_disable_edit_mode').click(function(){
    $('.sympal_slot_form.edit_enabled form input.cancel').click();
  });
});

/**
 * This will enable edit mode (if it's not already enabled, which will
 * toggle the display of the "edit-mode" button on the edit bar
 * 
 * @param boolean enable To enable/disable edit mode for the given slot form
 * @param jQuery slotForm the jQuery object repreenting the .slot_form that is being enabled/disabled
 */
function sympal_toggle_edit_mode(enable, slotForm)
{
  if (enable)
  {
    // mark the slot_form as being edited
    jQuery(slotForm).addClass('edit_enabled');
    
    // make sure the edit buttons are displayed
    $('.sympal_inline_edit_bar_edit_buttons').show();
  }
  else
  {
    // mark the slot_form as not being edited
    jQuery(slotForm).removeClass('edit_enabled');
    
    // check to see if any other slots are being edited - hide edit buttons if not
    if (jQuery('.sympal_slot_form.edit_enabled').length == 0)
    {
      $('.sympal_inline_edit_bar_edit_buttons').hide();
    }
  }
}