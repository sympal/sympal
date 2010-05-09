var currentlyFocusedSympalEditor = null;

$(function()
{

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
  
  // Make the assets button open up the chooser
  $('.toggle_sympal_assets').sympalChooser({
    chooserCallback: function(chosenValue, options)
    {
      insertString = '[asset:'+chosenValue;
      
      // add each option as index="value" to the string
      $.each(options, function(index, value) {
        insertString += ' '+index+'="'+value+'"';
      });
      
      insertString += ']';
      
      sympalInsertIntoCurrentEditor(insertString);
    }
  });
  
  // Make the links button open up the chooser
  $('.toggle_sympal_links').sympalChooser({
    chooserCallback: function(chosenValue, options)
    {
      insertString = '[link:'+chosenValue;
      
      // add each option as index="value" to the string
      $.each(options, function(index, value) {
        insertString += ' '+index+'="'+value+'"';
      });
      
      insertString += ']';
      
      sympalInsertIntoCurrentEditor(insertString);
    }
  });

  $.bindAllSlotEditors();
  
  // Hide the "Edit-mode" buttons
  $.sympalHideInlineEditButtons();
  
  // globally save slots on the "save" edit button
  $('#inline-edit-bar-buttons-menu .sympal_save_content_slots').click(function(){
    $('form.sympal_slot_form').submit();
  });
  
  // globally hide slot forms on the "cancel" edit button
  $('#inline-edit-bar-buttons-menu .sympal_disable_edit_mode').click(function(){
    $('form.sympal_slot_form input.cancel').click();
  });
});

// add some jQuery functions
$.extend({

  bindAllSlotEditors : function ()
  {
    // initialize each slot object
    $('.sympal_slot_wrapper').each(function() {
      $(this).sympalSlot({
        edit_mode: $(this).metadata().edit_mode,
        slotType: $(this).metadata().type,
        view_url:  $(this).metadata().view_url,
      }).bind('openEditor', function() {
        // Make sure the inline editor buttons are displayed
        $.sympal.editor.openEditors = $.sympal.editor.openEditors + 1;
        $.sympalShowInlineEditButtons();
      }).bind('closeEditor', function() {
        // On close, see if there are any editors still open
        $.sympal.editor.openEditors = $.sympal.editor.openEditors - 1;
        if ($.sympal.editor.openEditors == 0)
        {
          $.sympalHideInlineEditButtons();
        }
      });
    });
  }

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
    return;
  }

  if (currentlyFocusedSympalEditor !== null)
  {
    // Check if is tinymce
    if (currentlyFocusedSympalEditor.hasClass('slot_tinymce'))
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