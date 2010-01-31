<?php

function get_sympal_admin_menu_object()
{
  static $menu;

  if (!$menu)
  {
    $sympalContext = sfSympalContext::getInstance();
    $siteTitle = $sympalContext->getSite()->getTitle();
    $menu = new sfSympalMenuAdminMenu('Sympal Admin', '@sympal_dashboard');

    if ($sympalContext->isAdminModule())
    {
      $menu->addChild('Go to Site Frontend', '@homepage');
    }

    if (sfContext::getInstance()->getUser()->hasCredential('ViewDashboard'))
    {
      $menu->addChild('My Dashboard', '@sympal_dashboard');
    }

    if (sfContext::getInstance()->getUser()->hasCredential('ClearCache'))
    {
      $menu->addChild('Clear Cache', '@sympal_clear_cache');
    }

    $menu->addChild('Content', null, array('label' => $siteTitle.' Content'));
    $menu->addChild('Site Administration', null, array('label' => $siteTitle.' Setup'));
    $menu->addChild('Security', null, array('label' => 'Users & Security'));
    $menu->addChild('Administration', null, array('label' => 'Global Setup'));

    sfApplicationConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($menu, 'sympal.load_admin_menu'));

    $sympalContext = sfSympalContext::getInstance();
    $contentRecord = $sympalContext->getCurrentContent();
    $menuItem = $sympalContext->getCurrentMenuItem();

    if ($contentRecord)
    {
      sfApplicationConfiguration::getActive()->getEventDispatcher()->notify(
        new sfEvent($menu, 'sympal.load_editor', array(
          'content' => $contentRecord,
          'menuItem' => $menuItem
        )
      ));
    }
  }

  return $menu;
}

/**
 * Get the Sympal admin menu instances
 *
 * @return sfSympalMenuAdminMenu $menu
 */
function get_sympal_admin_menu()
{
  $menu = get_sympal_admin_menu_object();
  return get_partial('sympal_admin/menu', array('menu' => $menu));
}