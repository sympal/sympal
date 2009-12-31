$(function() {
  $('#sympal_links_container #links ul li a').click(function() {
    if (currentlyFocusedElement === null)
    {
      currentlyFocusedElement = $('.sympal_content_slot .editor textarea:first');
    }
    currentlyFocusedElement.insertAtCaret('[link:' + $(this).parents('li').attr('id') + ' label="' + $(this).html() + '"]');
    return false;
  });
});