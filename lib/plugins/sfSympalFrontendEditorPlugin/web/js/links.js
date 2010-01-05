$(function() {
  $('#sympal_links_container #links ul li a').click(function() {
    var text = '[link:' + $(this).parents('li').attr('id') + ' label="' + $(this).html() + '"]';
    sympalInsertIntoCurrentEditor(text);
    return false;
  });
});