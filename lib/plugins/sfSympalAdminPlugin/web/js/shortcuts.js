$(function() {
  shortcut.add('Ctrl+Q', function() {
    $('.sympal_signout_icon a').click();
  });

  shortcut.add('Ctrl+Shift+G', function() {
    location.href = $('#sympal_go_to_switch').attr('href');
  });
});