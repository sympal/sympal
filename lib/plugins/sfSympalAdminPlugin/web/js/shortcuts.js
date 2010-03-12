$(function() {
  shortcut.add('Ctrl+Q', function() {
    // FIXME: We should pass by alert message 'Are you sure?'
    // before quit with this shortcut.
    location.href = $('li#sympal-admin-signout a').attr('href');
  });

  shortcut.add('Ctrl+Shift+G', function() {
    location.href = $('#sympal_go_to_switch').attr('href');
  });
});
