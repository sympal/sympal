(function($) {

$.fn.extend({
  sfSympalSlotMarkdown: function(widget)
  {
    $('textarea.slot_markdown', widget.element)
      .markItUp(sympalMarkitupSettings);
  }
});

})(jQuery);