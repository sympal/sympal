$(function() {
  $('#sympal_objects_container #sympal_objects_list ul li a').click(function() {
    parentEle = $(this).parents('li');
    
    var text = '['+ parentEle.attr('rel') + ']';
    sympalInsertIntoCurrentEditor(text);
    
    return false;
  });
});