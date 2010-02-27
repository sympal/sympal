jQuery(document).ready(function(){  
  
  // popup editing
  $('a.edit_slot_button.popup').fancybox({
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
      selectedArray =  selectedArray + '';
      formSelector = selectedArray.substr(selectedArray.indexOf("#"));
      
      $(formSelector).hide();
    }
  });
  
  // inline editing
  $('a.edit_slot_button.in-place').click(function(){
    $(this).siblings('.edit_slot_form').show();
    $(this).siblings('.edit_slot_content').hide();
    
    $(this).hide();
    $(this).addClass('no-hover');
    
    return false;
  });
  
  // show the edit button when hovering over editable area
  $('.edit_slot_wrapper').hover(function(){
    $(this).find('.edit_slot_button:not(.no-hover)').show();
  }, function(){
    $(this).find('.edit_slot_button:not(.no-hover)').hide();
  });
  
  // highlight editable area on edit button hover
  $('a.edit_slot_button').hover(function(){
    $(this).next().css('opacity', .2)
    $(this).next().children().css('opacity', .2);
  },function(){
    $(this).next().css('opacity', 1)
    $(this).next().children().css('opacity', 1);
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