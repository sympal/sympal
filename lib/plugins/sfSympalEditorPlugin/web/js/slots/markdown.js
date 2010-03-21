(function($) {

$.fn.extend({
  sfSympalSlotMarkdown: function(editor)
  {
    $('textarea.slot_markdown', editor)
      .markItUp(sympalMarkitupSettings);
  }
});

})(jQuery);