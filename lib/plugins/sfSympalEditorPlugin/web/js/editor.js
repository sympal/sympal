// TODO: This javascript sucks, but it works. Make it not suck but keep it working.

var currentlyFocusedSympalEditor = null;

$(function()
{
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

  $('#sfWebDebugBar a:first').click(function() {
    if ($('#sfWebDebugDetails').css('visibility') == 'visible')
    {
      $('#sfWebDebugDetails').css('visibility', 'hidden');
    } else {
      $('#sfWebDebugDetails').css('visibility', 'visible');
    }
    sfWebDebugToggleMenu();
    return false;
  });

  // Hide the inline edit bar buttons by default
  // These are the Save and Preview buttons which will be shown when the user
  // clicks Edit
  $('.sympal_inline_edit_bar_buttons').hide();

  var editMode = $.cookie('sympal_inline_edit_mode');

  if (editMode == 'true')
  {
    toggleSympalEditMode();
  }

  // Function to toggle edit mode on and off
  var enabled = false;
  function toggleSympalEditMode(clicked)
  {
    enabled = enabled ? false : true;

    $.cookie('sympal_inline_edit_mode', enabled ? 'true' : 'false');

    if (clicked)
    {
      // Focus on the form element that was clicked to enable inline editing
      $($(clicked).parent().get(0)).find('.editor input, .editor textarea, .editor select:first').focus();
    }

    // If the state is being enabled then lets change the inline edit bar buttons and link
    if (enabled)
    {
      enabled = false;
      $('.toggle_edit_mode').hide();
      $('.sympal_inline_edit_bar_buttons').show();
      $('.sympal_content_slot .editor').show();
      $('.sympal_content_slot .value').hide()
    } else {
      $('.sympal_content_slot .editor').hide();
      $('.sympal_content_slot .value').show()
    }
  }

  // When an element with class="toggle_edit_mode" is clicked toggle edit mode
  $('input.toggle_edit_mode').click(function() {
    toggleSympalEditMode(this);
  });

  $('span.toggle_edit_mode').dblclick(function() {
    toggleSympalEditMode(this);
  });

  sympalSetupDropdownMenus();

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
    sympalTinyMCETriggerSave();
    
    // Save each content slot form
    $('.sympal_content_slot form').each(function() {
      var form = $(this);
      $(this).ajaxSubmit({
        target: form.parents('.sympal_content_slot').find('.value')
      });
    });
    // Toggle the editor and value states
    $('.sympal_content_slot .editor').hide();
    $('.sympal_content_slot .value').show();

    // Update the inline edit bar buttons and links
    $('.toggle_edit_mode').show();
    $('.sympal_inline_edit_bar_buttons').hide();

    $.cookie('sympal_inline_edit_mode', 'false');
    sympalCloseAllDropdowns();
  });

  // Render a preview of all the slots
  $('.sympal_preview_content_slots').click(function() {
    sympalTinyMCETriggerSave();
    
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
    $('.sympal_content_slot .editor').hide();
    $('.sympal_content_slot .value').show();

    // Update the inline edit bar buttons and links
    $('.toggle_edit_mode').show();
    $('.sympal_inline_edit_bar_buttons').hide();

    $.cookie('sympal_inline_edit_mode', 'false');
    sympalCloseAllDropdowns();
  });

  $('.sympal_disable_edit_mode').click(function() {
    // Toggle the editor and value states
    $('.sympal_content_slot .editor').hide();
    $('.sympal_content_slot .value').show();

    // Update the inline edit bar buttons and links
    $('.toggle_edit_mode').show();
    $('.sympal_inline_edit_bar_buttons').hide();

    $.cookie('sympal_inline_edit_mode', 'false');
    sympalCloseAllDropdowns();
    $('#sfWebDebug').show();
  });

  $('.sympal_content_slot .editor input, .sympal_content_slot .editor textarea').focus(function() {
    currentlyFocusedSympalEditor = $(this);
  });

  $('#sympal_close_menus').click(function() {
    return false;
  });

  $('.sympal_close_menu').live('click', function() {
    sympalCloseAllDropdowns();
  });
});

var timeout    = 1000;
var closetimer = 0;
var sympalMenuItem = 0;

function sympalAdminMenuOpen()
{
  sympalAdminMenuCancelTimer();
  sympalAdminMenuClose();
  sympalMenuItem = $(this).find('ul').show();
}

function sympalAdminMenuClose()
{
  if (sympalMenuItem)
  {
    sympalMenuItem.hide();
  }
}

function sympalAdminMenuTimer()
{
  closetimer = window.setTimeout(sympalAdminMenuClose, timeout);
}

function sympalAdminMenuCancelTimer()
{
  if (closetimer)
  {
    window.clearTimeout(closetimer);
    closetimer = null;
  }
}

$ (document).ready(function()
{
  $('.sympal_inline_edit_admin_menu ul > li').bind('mouseover', sympalAdminMenuOpen);
});

document.onclick = sympalAdminMenuClose;

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
  $('#sympal_dashboard').hide();
  $('#sympal_editor').hide();
  $('#sympal_assets').hide();
  $('#sympal_links').hide();
}

function sympalSetupDropdownMenus()
{
  sympalSetupDropdownMenu('#sympal_dashboard', '.toggle_dashboard_menu');
  sympalSetupDropdownMenu('#sympal_assets', '.toggle_sympal_assets');
  sympalSetupDropdownMenu('#sympal_links', '.toggle_sympal_links');
}
