$(function() {
  // Override the webdebug toolbar and how it is hidden and displayed
  $('#sfWebDebugBar a:first').click(function() {
    if ($('#sfWebDebugDetails').css('visibility') == 'visible')
    {
      $('#sfWebDebugDetails').css('visibility', 'hidden');
    } else {
      $('#sfWebDebugDetails').css('visibility', 'visible');
    }
    sfWebDebugToggleMenu();
    return false;
  });
});