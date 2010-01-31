$(function() {
  shortcut.add('Ctrl+Q', function() {
    $('.sympal_signout_icon a').click();
  });

  // Top admin menu bar mouseover and mouseout events for dropdown menus
  $('.sympal_admin_menu ul > li').bind('mouseover', sympalAdminMenuOpen);
  $('.sympal_admin_menu ul > li').bind('mouseout', sympalAdminMenuClose);

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

// Top admin menu bar
var timeout    = 1000;
var closetimer = 0;
var sympalMenuItem = 0;

function sympalAdminMenuOpen()
{
  sympalAdminMenuCancelTimer();
  sympalAdminMenuClose();
  sympalMenuItem = $(this).find('ul').show();
}

function sympalAdminMenuClose()
{
  if (sympalMenuItem)
  {
    sympalMenuItem.hide();
  }
}

function sympalAdminMenuTimer()
{
  closetimer = window.setTimeout(sympalAdminMenuClose, timeout);
}

function sympalAdminMenuCancelTimer()
{
  if (closetimer)
  {
    window.clearTimeout(closetimer);
    closetimer = null;
  }
}