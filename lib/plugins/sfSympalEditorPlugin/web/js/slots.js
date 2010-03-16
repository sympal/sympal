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
    
    // 1) actually initialize one of the two editors
    // 2) add buttons
    // 3) setup ajax form save
    
    href = $('.sympal_slot_button', this.element).attr('href');
    if (this._getData('edit_mode') == 'in-place')
    {
      var editor = this.openInlineEditor(href);
    }
    else
    {
      var editor = this.openPopupEditor(href);
    }
    return false;
    editor.bind('bootstrap', function() {
      alert('bootstrapping');
    });
  },
  
  openPopupEditor : function(href)
  {
    return $.fancybox(href, {
      'zoomSpeedIn': 300,
      'zoomSpeedOut': 300,
      'overlayShow': true,
      'hideOnContentClick': false,
      'type': 'ajax'
    });
  },
  
  openInlineEditor : function(href)
  {
    slotContent = $('.sympal_slot_content', this.element);
    slotContent.load(href);
    
    return slotContent;
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

/*
 * Call this on form submit to ajax-submit a slot form
 */
function sympal_slot_form_submit(formEle)
{
  sympal_pre_slot_submit(formEle);
  
  formEle.ajaxSubmit({
    target: formEle.find('.form_body'),
    success: sympal_slot_submit_success
  });
}

/**
 * Called right before a slot is ajaxed submitted
 */
function sympal_pre_slot_submit(formEle)
{
  // in the event that it's tinymce, trigger save
  sympalTinyMCETriggerSave();
  formEle.find('img.loading_anim').show();
  $('#sympal_slot_flash').slideUp();
}

/*
 * Called on form submit success. This may need to be changed for
 * jquery 1.4: http://jquery.malsup.com/form/#options-object
 */
function sympal_slot_submit_success(responseText, statusText, formEle)
{
  formEle.find('img.loading_anim').hide();
}

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