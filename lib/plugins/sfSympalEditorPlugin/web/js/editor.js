var currentlyFocusedSympalEditor = null;

$(function()
{
  shortcut.add('Ctrl+P', function() {
    $('.sympal_inline_edit_bar_publish a').click();
  });

  // Content publishing fancybox
  $('#sympal_assets #sympal_ask_confirmation input.no').live('click', function() {
    $('#sympal_assets').load($('.toggle_sympal_assets').attr('rel'));
    return false;
  });

  $('#fancy_content #sympal_ask_confirmation input.no').live('click', function() {
    $.fn.fancybox.close();
    return false;
  });

  $('#fancy_content #sympal_ask_confirmation input.yes').live('click', function() {
    $.fn.fancybox.close();
    setTimeout(function() {
      location.href = location.href;
    }, 750);
  });

  $('.sympal_inline_edit_bar_publish a').fancybox({
    'zoomSpeedIn': 300,
    'zoomSpeedOut': 300,
    'overlayShow': true,
    'hideOnContentClick': false,
  });

  // Toggle edit mode
  var editMode = $.cookie('sympal_inline_edit_mode');

  if (editMode == 'true')
  {
    sympalEnableEditMode();
  }

  // When a input element with class="toggle_edit_mode" is clicked toggle edit mode
  $('input.toggle_edit_mode').click(function() {
    sympalToggleEditMode(this);
  });

  // When a span element with class="toggle_edit_mode" is double clicked toggle edit mode
  $('span.toggle_edit_mode').dblclick(function() {
    sympalToggleEditMode(this);
  });

  // Show the change slot type dropdown when class="sympal_change_slot_type" is clicked
  $('.sympal_change_slot_type').click(function() {
    $(this).siblings('.sympal_change_slot_type_dropdown').slideToggle();
  });

  // Update the editor form for this slot when the dropdown is changed
  $('.sympal_change_slot_type_dropdown select').change(function() {
    var select = $(this);
    var form = $(this.form);
    var type = select.val();
    var slotId = form.parents('span').find('.content_slot_id').attr('value');
    var contentId = form.parents('span').find('.content_id').attr('value');

    var url = $('#sympal_base_url').attr('value') + 'change_content_slot_type/' + contentId + '/' + slotId + '/' + type;

    form.parents('.sympal_content_slot').find('.editor .sympal_content_slot_editor_form').load(url, function() {
      select.parents('.sympal_change_slot_type_dropdown').slideToggle()
    });
  });

  // Save content slots
  $('.sympal_save_content_slots').click(function() {
    sympalSaveContentSlots();
  });

  // Preview content slots
  $('.sympal_preview_content_slots').click(function() {
    sympalSaveContentSlots(true);
  });

  // Disable edit mode
  $('.sympal_disable_edit_mode').click(function() {
    sympalDisableEditMode();
  });

  // Keep track of the currently focused editor
  $('.sympal_content_slot .editor input, .sympal_content_slot .editor textarea').focus(function() {
    currentlyFocusedSympalEditor = $(this);
  });

  // Close all dropdowns with class="sympal_close_menu" is clicked
  $('.sympal_close_menu').live('click', function() {
    sympalCloseAllDropdowns();
  });

  $('.sympal_content_slot_error').live('mouseover', function () {
    sympalHighlightContentSlot($(this).attr('rel'));
  });

  $('#sympal_slot_errors #close').live('click', function() {
    $('#sympal_slot_errors_icon').show();
    $('#sympal_slot_errors').slideToggle();
  });
  
  $('#sympal_slot_errors_icon').live('click', function() {
    $('#sympal_slot_errors').slideToggle();
  });

  // Setup the sympal dropdown menu panels (assets and links)
  sympalSetupDropdownMenus();
});

function sympalToggleEditMode(clicked)
{
  var editMode = $.cookie('sympal_inline_edit_mode');

  if (clicked)
  {
    // Focus on the form element that was clicked to enable inline editing
    $($(clicked).parent().get(0)).find('.editor input, .editor textarea, .editor select:first').focus();
  }

  // If the state is being enabled then lets change the inline edit bar buttons and link
  if (editMode == 'true')
  {
    sympalDisableEditMode();
  } else {
    sympalEnableEditMode();
  }
}

function sympalSaveContentSlots(preview, disableEditMode)
{
  $('.sympal_inline_edit_loading').show();

  preview = typeof(preview) != 'undefined' ? preview : false;
  disableEditMode = typeof(disableEditMode) != 'undefined' ? disableEditMode : false;
  if (preview)
  {
    disableEditMode = true;
  }

  sympalTinyMCETriggerSave();

  var queryString;
  $('.sympal_content_slot form').each(function() {
    var form = $(this);
    var slotId = form.parents('span').find('.content_slot_id').attr('value');
    queryString = queryString + '&' + form.formSerialize() + '&slot_ids[]=' + slotId;
  });

  var url = $('#sympal_save_slots_url').attr('value');
  url = preview ? url + '?preview=1' : url;
  $.post(url, queryString, function(response) {
    // If response doesn't contain !Errors! then we know no errors occurred
    // so we can disable edit mode
    if (response.indexOf('!Errors!') == -1)
    {
      $('#sympal_slot_errors').html('');

      if (disableEditMode)
      {
        sympalDisableEditMode();
      }
    }

    eval(response);

    $('.sympal_inline_edit_loading').hide();
  });
}

function sympalHighlightContentSlot(id)
{
  $('#sympal_content_slot_' + id + ' input, #sympal_content_slot_' + id + ' textarea').focus();
}

function sympalDisableEditMode()
{
  $('.toggle_edit_mode').show();
  $('.sympal_content_slot .value').show();
  $('.sympal_inline_edit_bar_edit_buttons').hide();
  $('.sympal_content_slot .editor').hide();

  $('#sympal_slot_errors').slideUp();
  $('#sympal_slot_errors_icon').hide();

  $.cookie('sympal_inline_edit_mode', 'false');
  sympalCloseAllDropdowns();
}

function sympalEnableEditMode()
{
  $('.toggle_edit_mode').hide();
  $('.sympal_content_slot .value').hide()
  $('.sympal_inline_edit_bar_edit_buttons').show();
  $('.sympal_content_slot .editor').show();

  if ($('#sympal_slot_errors').html())
  {
    $('#sympal_slot_errors_icon').show();
    $('#sympal_slot_errors').slideDown();
  }

  $.cookie('sympal_inline_edit_mode', 'true');
}

function sympalTinyMCETriggerSave()
{
  tinyMCE.triggerSave();
}

function sympalHandleTinyMCEEvent(e)
{
  if (e.type == 'click')
  {
    currentlyFocusedSympalEditor = $('#' + tinyMCE.activeEditor.id);
  }
}

function sympalInsertIntoCurrentEditor(text)
{
  if (currentlyFocusedSympalEditor === null)
  {
    currentlyFocusedSympalEditor = $('.sympal_content_slot .editor textarea:first');
  }

  if (currentlyFocusedSympalEditor !== null)
  {
    // Check if is tinymce
    if (currentlyFocusedSympalEditor.parents('span').hasClass('sympal_tiny_mce_content_slot_editor_form'))
    {
      tinyMCE.execInstanceCommand(currentlyFocusedSympalEditor.attr('id'), 'mceInsertContent', false, text);
    } else {
      currentlyFocusedSympalEditor.insertAtCaret(text);
    }
  }
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

function sympalSetupDropdownMenu(id, control)
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
            sympalCloseAllDropdowns();
          }
        });
      } else {
        $(id).slideToggle('fast');
        sympalCloseAllDropdowns();
      }
    } else {
      $(id).slideToggle('fast');
      sympalCloseAllDropdowns();
    }
  });
}

function sympalCloseAllDropdowns()
{
  $('#sympal_editor').hide();
  $('#sympal_assets').hide();
  $('#sympal_links').hide();
  $('#sympal_objects').hide();
}

function sympalSetupDropdownMenus()
{
  sympalSetupDropdownMenu('#sympal_assets', '.toggle_sympal_assets');
  sympalSetupDropdownMenu('#sympal_links', '.toggle_sympal_links');
  sympalSetupDropdownMenu('#sympal_objects', '.toggle_sympal_objects');
}