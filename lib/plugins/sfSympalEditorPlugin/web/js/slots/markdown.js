(function($) {

$.fn.extend({
  sfSympalSlotMarkdown: function(editor)
  {
    $('textarea.slot_markdown', editor.element)
      .markItUp(sympalMarkitupSettings);
  }
});

})(jQuery);