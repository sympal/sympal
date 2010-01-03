// TODO: This javascript sucks, but it works. Make it not suck but keep it working.

var currentlyFocusedElement = null;

$(function()
{
  // Hide the inline edit bar buttons by default
  // These are the Save and Preview buttons which will be shown when the user
  // clicks Edit
  $('.sympal_inline_edit_bar_buttons').hide();

  var editMode = $.cookie('sympal_inline_edit_mode');

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
      $('.toggle_edit_mode').hide();
      $('.sympal_inline_edit_bar_buttons').show();
    }
  }

  // When an element with class="toggle_edit_mode" is clicked toggle edit mode
  $('input.toggle_edit_mode').click(function() {
    toggleEditMode(this);
  });

  $('span.toggle_edit_mode').dblclick(function() {
    toggleEditMode(this);
  });

  setupDropdownMenus();

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
    $('.toggle_edit_mode').show();
    $('.sympal_inline_edit_bar_buttons').hide();

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
    $('.toggle_edit_mode').show();
    $('.sympal_inline_edit_bar_buttons').hide();

    $.cookie('sympal_inline_edit_mode', 'false');
  });

  $('.sympal_disable_edit_mode').click(function() {
    // Toggle the editor and value states
    $('.sympal_content_slot .editor').toggle();
    $('.sympal_content_slot .value').toggle();

    // Update the inline edit bar buttons and links
    $('.toggle_edit_mode').show();
    $('.sympal_inline_edit_bar_buttons').hide();

    $.cookie('sympal_inline_edit_mode', 'false');
    closeAllDropdowns();
  });

  $('.sympal_content_slot .editor input, .sympal_content_slot .editor textarea').focus(function() {
    currentlyFocusedElement = $(this);
  });

  $('#sympal_close_menus').click(function() {
    return false;
  });

  $('.sympal_close_menu').live('click', function() {
    closeAllDropdowns();
  });
});

function sympalTinyMCETriggerSave()
{
  tinyMCE.triggerSave();
}

$.fn.insertAtCaret = function (myValue) {
  return this.each(function() {
    //IE support
    if (document.selection) {
      this.focus();
      sel = document.selection.createRange();
      sel.text = myValue;
      this.focus();
    }
    //MOZILLA/NETSCAPE support
    else if (this.selectionStart || this.selectionStart == '0')
    {
      var startPos = this.selectionStart;
      var endPos = this.selectionEnd;
      var scrollTop = this.scrollTop;
      this.value = this.value.substring(0, startPos) + myValue + this.value.substring(endPos, this.value.length);
      this.focus();
      this.selectionStart = startPos + myValue.length;
      this.selectionEnd = startPos + myValue.length;
      this.scrollTop = scrollTop;
    } else {
      this.value += myValue;
      this.focus();
    }
  });
};

function setupDropdownMenu(id, control)
{
  $(id).hide();

  $(control).click(function() {
    if ($(this).attr('rel'))
    {
      if (!$(id).html())
      {
        $(id).load($(this).attr('rel'), {
          success: function() {
            $(id).slideToggle('fast');
            closeAllDropdowns();
          }
        });
      } else {
        $(id).slideToggle('fast');
        closeAllDropdowns();
      }
    } else {
      $(id).slideToggle('fast');
      closeAllDropdowns();
    }
  });
}

function closeAllDropdowns()
{
  $('#sympal_dashboard').hide();
  $('#sympal_editor').hide();
  $('#sympal_assets').hide();
  $('#sympal_links').hide();
}

function setupDropdownMenus()
{
  setupDropdownMenu('#sympal_dashboard', '.toggle_dashboard_menu');
  setupDropdownMenu('#sympal_editor', '.toggle_editor_menu');
  setupDropdownMenu('#sympal_assets', '.toggle_sympal_assets');
  setupDropdownMenu('#sympal_links', '.toggle_sympal_links');
}