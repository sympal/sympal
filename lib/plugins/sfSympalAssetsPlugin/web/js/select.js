$(function() {
  $('#sympal_assets_list li.up a, #sympal_assets_list li.folder a').click(function() {
    $('#sympal_assets_container').load(this.href);
    return false;
  });

  $('#sympal_assets_list li.asset a').click(function() {
    $('#sf_sympal_content_slot_value').insertAtCaret('[asset:' + $(this).parents('li').attr('id') + ']');
    return false;
  });
});