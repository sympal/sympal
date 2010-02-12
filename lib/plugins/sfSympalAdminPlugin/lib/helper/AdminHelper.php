<?php

function get_sympal_admin_menu_object($class = 'sfSympalMenuAdminMenu')
{
  static $menu;

  if (!$menu)
  {
    $sympalContext = sfSympalContext::getInstance();
    $menu = new $class('Sympal Admin', '@sympal_dashboard');

    if ($sympalContext->isAdminModule())
    {
      $menu->addChild(sprintf('Go to %s', $sympalContext->getSite()->getTitle()), '@homepage', 'id=sympal_go_to_switch');
    }
    else if (sfContext::getInstance()->getUser()->hasCredential('ViewDashboard'))
    {
      $menu->addChild('Go to Admin', '@sympal_dashboard', 'id=sympal_go_to_switch');
    }


    if (sfContext::getInstance()->getUser()->hasCredential('ClearCache'))
    {
      $menu->addChild('Clear Cache', '@sympal_clear_cache', 'id=sympal_clear_cache_fancybox');
    }

    $menu->addChild('Content', '@sympal_content', array('label' => 'Site Content'));
    $menu->addChild('Site Administration', '@sympal_sites_edit?id='.$sympalContext->getSite()->getId(), array('label' => 'Site Setup'));
    $menu->addChild('Security', '@sympal_users', array('label' => 'Users & Security'));
    $menu->addChild('Administration', '@sympal_sites', array('label' => 'Global Setup'));

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