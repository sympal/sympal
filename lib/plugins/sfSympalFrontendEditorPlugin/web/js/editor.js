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

  $('.sympal_inline_edit_bar_buttons').hide();

  if (location.hash == '#edit')
  {
    toggleEditMode();
  }

  var enabled = false;

  function toggleEditMode()
  {
    enabled = enabled ? false : true;

    $('.sympal_content_slot .editor').toggle();
    $($(this).parent().get(0)).find('.editor input, .editor textarea, .editor select:first').focus();

    $('.sympal_content_slot .value').toggle();

    if (enabled)
    {
      enabled = false;
      $('a.toggle_edit_mode').hide();
      $('.sympal_inline_edit_bar_buttons').show();
    }
  }

  $('.toggle_edit_mode').click(function() {
    toggleEditMode();
  });

  $('.sympal_change_slot_type').click(function() {
    $('.sympal_change_slot_type_dropdown').slideToggle();
  });

  $('.sympal_change_slot_type_dropdown select').change(function() {
    var select = $(this);
    var form = $(this.form);
    var url = form.attr('action').replace('edit', 'change').replace('/save', '');
    form.ajaxSubmit({
      url: url,
      target: form.parents('.sympal_content_slot').find('.editor .sympal_content_slot_editor_form'),
      success: select.parents('.sympal_change_slot_type_dropdown').slideToggle()
    });
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

    $('a.toggle_edit_mode').show();
    $('.sympal_inline_edit_bar_buttons').hide();
    location.hash = '';
  });

  $('.sympal_preview_content_slots').click(function() {
    $('.sympal_content_slot form').each(function() {
      var form = $(this);
      var url = form.attr('action').replace('edit', 'preview').replace('/save', '');
      $(this).ajaxSubmit({
        url: url,
        target: form.parents('.sympal_content_slot').find('.value')
      });
      location.hash = '';
    });
    $('.sympal_content_slot .editor').toggle();
    $('.sympal_content_slot .value').toggle();

    $('a.toggle_edit_mode').show();
    $('.sympal_inline_edit_bar_buttons').hide();
  });
});