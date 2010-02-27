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

  // Handle "no" on confirmation popup: just close the popup
  $('#fancybox-wrap #sympal_ask_confirmation input.no').live('click', function() {
    $.fancybox.close();
    return false;
  });

  // Handle "yes" on confirmation popup: let the form act, then refresh the page
  $('#fancybox-wrap #sympal_ask_confirmation input.yes').live('click', function() {
    $.fancybox.close();
    setTimeout(function() {
      location.href = location.href;
    }, 750);
  });

  // opens the publish/unpublish dialog into fancybox
  $('.sympal_inline_edit_bar_publish a').fancybox({
    'zoomSpeedIn': 300,
    'zoomSpeedOut': 300,
    'overlayShow': true,
    'hideOnContentClick': false,
  });

  // Keep track of the currently focused editor
  $('.sympal_slot_wrapper form input, .sympal_slot_wrapper form textarea').focus(function() {
    currentlyFocusedSympalEditor = $(this);
  });

  // Close all dropdown menus when class="sympal_close_menu" is clicked
  $('.sympal_close_menu').live('click', function() {
    sympalCloseAllDropdowns();
  });

  // Setup the sympal dropdown menu panels (e.g. assets and links)
  sympalSetupDropdownMenus();
});

// Must be called before saving a tinymce field via ajax
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