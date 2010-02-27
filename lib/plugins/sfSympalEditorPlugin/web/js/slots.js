jQuery(document).ready(function(){  
  
  // popup editing
  $('a.sympal_slot_button.popup').fancybox({
    'zoomSpeedIn': 300,
    'zoomSpeedOut': 300,
    'overlayShow': true,
    'hideOnContentClick': false,
    'onStart': function(selectedArray, selectedIndex, selectedOpts) {
      selectedArray =  selectedArray + '';
      formSelector = selectedArray.substr(selectedArray.indexOf("#"));
      
      $(formSelector).show();
    },
    'onCleanup': function(selectedArray, selectedIndex, selectedOpts) {
      $('#fancybox-inner .sympal_slot_form').hide();
    }
  });
  
  // inline editing
  $('a.sympal_slot_button.in-place').click(function(){
    sympal_toggle_inline_edit_slot($(this).parents('.sympal_slot_wrapper').eq(0), true);
    
    return false;
  });
  
  // show the edit button when hovering over editable area
  $('.sympal_slot_wrapper').hover(function(){
    $(this).find('.sympal_slot_button:not(.no-hover)').show();
  }, function(){
    $(this).find('.sympal_slot_button:not(.no-hover)').hide();
  });
  
  // highlight editable area on edit button hover
  $('a.sympal_slot_button').hover(function(){
    $(this).next().css('opacity', .2)
    $(this).next().children().css('opacity', .2);
  },function(){
    $(this).next().css('opacity', 1)
    $(this).next().children().css('opacity', 1);
  });
  
  // the cancel button for when you're editing
  $('.sympal_slot_wrapper form input.cancel').click(function(){
    if ($(this).parents('form').eq(0).hasClass('popup'))
    {
      // popup closing
      $.fancybox.close();
    }
    else
    {
      // inline closing
      wrapperEle = $(this).parents('.sympal_slot_wrapper').eq(0);
      sympal_toggle_inline_edit_slot(wrapperEle, false);
    }
  });
});

/**
 * Called right before a slot is ajaxed submitted
 */
function sympalPreSlotSubmit(data, formEle, options)
{
  formEle.find('img.loading_anim').show();
  $('#sympal_slot_flash').slideUp();
}

/*
 * Called on form submit success. This may need to be changed for
 * jquery 1.4: http://jquery.malsup.com/form/#options-object
 */
function sympalSlotSubmitSuccess(responseText, statusText, formEle)
{
  formEle.find('img.loading_anim').hide();
}

/**
 * Toggles inline editing for the given slot id.
 * 
 * wrapperEle can be the wrapper element or the id to the wrapper element
 * 
 * Set toggle to true to enable editing, false to disable it
 */
function sympal_toggle_inline_edit_slot(wrapper, toggle)
{
  wrapper = $(wrapper);
  editButton = wrapper.find('.sympal_slot_button');
  
  if (toggle)
  {
    wrapper.find('.sympal_slot_form').show();
    wrapper.find('.sympal_slot_content').hide();
    
    editButton.hide();
    editButton.addClass('no-hover');
  }
  else
  {
    wrapper.find('.sympal_slot_form').hide();
    wrapper.find('.sympal_slot_content').show();
    
    editButton.show();
    editButton.removeClass('no-hover');
  }
}