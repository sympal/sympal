$(function()
{
  // Remember the state of the panel with a cokie
  if ($.cookie('sympal_editor_open') === null)
  {
    $.cookie('sympal_editor_open', 'false');
  }

  if ($.cookie('sympal_editor_open') == 'true')
  {
    $('div#sympal_editor').css({marginLeft:'-20px'});
  }

  $('#sympal_editor')
    .css({
      top: $.cookie('sympal_editor_bar_y'),
      left: $.cookie('sympal_editor_bar_x')
    })
    .draggable({
      stop: function (event, ui) {
        $.cookie('sympal_editor_bar_x', ui.position.left + 'px');
        $.cookie('sympal_editor_bar_y', ui.position.top + 'px');
      }
    }
  );

  // Hide the inline edit bar buttons by default
  // These are the Save and Preview buttons which will be shown when the user
  // clicks Edit
  $('.sympal_inline_edit_bar_buttons').hide();

  var editMode = $.cookie('sympal_inline_edit_mode');

  if (editMode === null)
  {
    editMode = 'false';
  }
  // If url hash is #edit lets enable edit mode
  if (location.hash == '#edit')
  {
    editMode = 'true';
    $.cookie('sympal_inline_edit_mode', 'true');
  }

  if (editMode == 'true')
  {
    toggleEditMode();
  }

  // Function to toggle edit mode on and off
  var enabled = false;
  function toggleEditMode(clicked)
  {
    enabled = enabled ? false : true;

    $.cookie('sympal_inline_edit_mode', enabled ? 'true' : 'false');

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
    $.cookie('sympal_inline_edit_mode', 'false');
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
    });
    // Toggle the editor and value states
    $('.sympal_content_slot .editor').toggle();
    $('.sympal_content_slot .value').toggle();

    // Update the inline edit bar buttons and links
    $('a.toggle_edit_mode').show();
    $('.sympal_inline_edit_bar_buttons').hide();

    location.hash = '';
    $.cookie('sympal_inline_edit_mode', 'false');
  });

  $('.sympal_disable_edit_mode').click(function() {
    // Toggle the editor and value states
    $('.sympal_content_slot .editor').toggle();
    $('.sympal_content_slot .value').toggle();

    // Update the inline edit bar buttons and links
    $('a.toggle_edit_mode').show();
    $('.sympal_inline_edit_bar_buttons').hide();

    location.hash = '';
    $.cookie('sympal_inline_edit_mode', 'false');
  });

  $('.sympal_inline_edit_bar')
    .css({
      top: $.cookie('sympal_inline_edit_bar_y'),
      left: $.cookie('sympal_inline_edit_bar_x')
    })
    .draggable({
      stop: function (event, ui) {
        $.cookie('sympal_inline_edit_bar_x', ui.position.left + 'px');
        $.cookie('sympal_inline_edit_bar_y', ui.position.top + 'px');
      }
    }
  );
});

function sympalTinyMCETriggerSave()
{
  tinyMCE.triggerSave();
}