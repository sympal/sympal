jQuery(document).ready(function(){
  jQuery('.sympal_slot_editor ul.slot_controls .edit').click(function(){
    slotWrapper = jQuery(this).parents('.sympal_slot_editor').eq(0);
    sympalToggleSlotEditor(slotWrapper);
    
    return false;
  });
  
  $('a.edit_slot_button').fancybox({
    'zoomSpeedIn': 300,
    'zoomSpeedOut': 300,
    'overlayShow': true,
    'hideOnContentClick': false,
  });
  
  $('a.edit_slot_button').hover(function(){
    $(this).next().css('opacity', .2)
    $(this).next().children().css('opacity', .2);
  },function(){
    $(this).next().css('opacity', 1)
    $(this).next().children().css('opacity', 1);
  });
  
  $('.edit_slot_wrapper').hover(function(){
    $(this).find('.edit_slot_button').show();
  }, function(){
    $(this).find('.edit_slot_button').hide();
  });
});

/**
 * Enables edit mode for the given slotWrapper (.sympal_slot_editor)
 */
function sympalToggleSlotEditor(slotWrapper)
{
  slotWrapper.next().toggle();
  slotWrapper.find('form').toggle();
}