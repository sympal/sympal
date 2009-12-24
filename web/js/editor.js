$(function()
{
  $('#sympal_toggle_editor').css('height', $('#sympal_editor').css('height'));

  if ($.cookie('sympal_editor_open') === null)
  {
    $.cookie('sympal_editor_open', 'false');
  }

  if ($.cookie('sympal_editor_open') == 'true')
  {
    $('div#sympal_editor').css({marginLeft:'-20px'});
  }

  hiConfig = {
      sensitivity: 1, // number = sensitivity threshold (must be 1 or higher)
      interval: 100, // number = milliseconds for onMouseOver polling interval
      timeout: 100, // number = milliseconds delay before onMouseOut
      over: function() {
          if ($.cookie('sympal_editor_open') == 'true')
          {
            $.cookie('sympal_editor_open', 'false');
            $('div#sympal_editor').animate({marginLeft:'-500px'}, 'slow');
          }
          else
          {
            $.cookie('sympal_editor_open', 'true');
            $('div#sympal_editor').animate({marginLeft:'-20px'}, 'slow');
          }
          
      }
  }
  $('#sympal_toggle_editor').hoverIntent(hiConfig);

  $('.sympal_content_slot_editor .cancel').click(function()
  {
    $($(this).parent.id).find('.editor').hide();
    $($(this).parent.id).find('.value').show()
  });

  $('.sympal_save_content_slots').hide();

  var enabled = false;
  $('.toggle_edit_mode').click(function() {
    enabled = enabled ? false : true;

    $('.sympal_content_slot .editor').toggle();
    $('.sympal_content_slot .value').toggle();

    if (enabled)
    {
      enabled = false;
      $('.toggle_edit_mode').hide();
      $('.sympal_save_content_slots').show();
    }
  });

  $('.sympal_save_content_slots').click(function() {
    $('.sympal_content_slot form').each(function() {
      var form = $(this);
      $(this).ajaxSubmit({
        target: form.parents('.sympal_content_slot').find('.value')
      });
    });
    $('.sympal_content_slot .editor').toggle();
    $('.sympal_content_slot .value').toggle();

    $('.toggle_edit_mode').show();
    $('.sympal_save_content_slots').hide();
  });
});