$(function()
{
  // Initialize sympal editor on left side of screen for content records
  // It shows when you hover over the bar on the left side of the screen
  $('#sympal_toggle_editor').css('height', $('#sympal_editor').css('height'));

  // Remember the state of the panel with a cokie
  if ($.cookie('sympal_editor_open') === null)
  {
    $.cookie('sympal_editor_open', 'false');
  }

  if ($.cookie('sympal_editor_open') == 'true')
  {
    $('div#sympal_editor').css({marginLeft:'-20px'});
  }

  // Setup hover intent for the bar
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

  // Hide the inline edit bar buttons by default
  // These are the Save and Preview buttons which will be shown when the user
  // clicks Edit
  $('.sympal_inline_edit_bar_buttons').hide();

  // If url hash is #edit lets enable edit mode
  if (location.hash == '#edit')
  {
    toggleEditMode();
  }

  // Function to toggle edit mode on and off
  var enabled = false;
  function toggleEditMode(clicked)
  {
    enabled = enabled ? false : true;

    // Toggle the editor state
    $('.sympal_content_slot .editor').toggle();

    if (clicked)
    {
      // Focus on the form element that was clicked to enable inline editing
      $($(clicked).parent().get(0)).find('.editor input, .editor textarea, .editor select:first').focus();
    }

    // Toggle the value state
    $('.sympal_content_slot .value').toggle();

    // If the state is being enabled then lets change the inline edit bar buttons and link
    if (enabled)
    {
      enabled = false;
      $('a.toggle_edit_mode').hide();
      $('.sympal_inline_edit_bar_buttons').show();
    }
  }

  // When an element with class="toggle_edit_mode" is clicked toggle edit mode
  $('.toggle_edit_mode').click(function() {
    toggleEditMode(this);
  });

  // Show the change slot type dropdown when class="sympal_change_slot_type" is clicked
  $('.sympal_change_slot_type').click(function() {
    $(this).siblings('.sympal_change_slot_type_dropdown').slideToggle();
  });

  // Update the editor widget for this slot when the dropdown is changed
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

  // Save all the slots on the page
  $('.sympal_save_content_slots').click(function() {
    // Save each content slot form
    $('.sympal_content_slot form').each(function() {
      var form = $(this);
      $(this).ajaxSubmit({
        target: form.parents('.sympal_content_slot').find('.value')
      });
    });
    // Toggle the editor and value states
    $('.sympal_content_slot .editor').toggle();
    $('.sympal_content_slot .value').toggle();

    // Update the inline edit bar buttons and links
    $('a.toggle_edit_mode').show();
    $('.sympal_inline_edit_bar_buttons').hide();
    location.hash = '';
  });

  // Render a preview of all the slots
  $('.sympal_preview_content_slots').click(function() {
    // Submit each slot form to render a preview
    $('.sympal_content_slot form').each(function() {
      var form = $(this);
      var url = form.attr('action').replace('edit', 'preview').replace('/save', '');
      $(this).ajaxSubmit({
        url: url,
        target: form.parents('.sympal_content_slot').find('.value')
      });
      location.hash = '';
    });
    // Toggle the editor and value states
    $('.sympal_content_slot .editor').toggle();
    $('.sympal_content_slot .value').toggle();

    // Update the inline edit bar buttons and links
    $('a.toggle_edit_mode').show();
    $('.sympal_inline_edit_bar_buttons').hide();
  });
});