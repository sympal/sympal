$(function() {
  $('#sympal_links_container ul li a').click(function() {
    $('#sf_sympal_content_slot_value').insertAtCaret('[link:' + $(this).parents('li').attr('id') + ']');
    return false;
  });
});