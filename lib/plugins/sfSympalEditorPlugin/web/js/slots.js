jQuery(document).ready(function(){  
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
 * Called right before a slot is ajaxed submitted
 */
function sympalPreSlotSubmit()
{
  $('#sympal_slot_flash').slideUp();
}