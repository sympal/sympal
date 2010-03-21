(function($) {

$.fn.extend({
  sfSympalSlotMarkdown: function(slot)
  {
    $('textarea.slot_markdown', slot.getEditor())
      .markItUp(sympalMarkitupSettings);
  }
});

})(jQuery);