(function($) {

$.fn.extend({
  sfSympalSlotTinyMCE: function(editor)
  {
    editor.getForm().bind('preFormSubmit', function() {
      tinyMCE.triggerSave();
    });
  }
});

})(jQuery);