<?php

/**
 * Helper for admin-related view tasks
 * 
 * @package     sfSympalAdminPlugin
 * @subpackage  helper
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */
function get_sympal_admin_menu_object($class = 'sfSympalMenuAdminMenu')
{
  static $menu;

  if (!$menu)
  {
    $sympalContext = sfSympalContext::getInstance();
    $siteManager = $sympalContext->getService('site_manager');
    $menu = new $class('Sympal Admin', '@sympal_dashboard');

    if ($sympalContext->getSympalConfiguration()->isAdminModule())
    {
      $menu->addChild(sprintf(__('Go to %s'), $siteManager->getSite()->getTitle()), '@homepage', 'id=sympal_go_to_switch');
    }
    else if (sfContext::getInstance()->getUser()->hasCredential('ViewDashboard'))
    {
      $menu->addChild('Admin', '@sympal_dashboard', 'id=sympal_go_to_switch');
    }


    if (sfContext::getInstance()->getUser()->hasCredential('ClearCache'))
    {
      $menu->addChild('Clear Cache', '@sympal_clear_cache', 'id=sympal_clear_cache_fancybox');
    }

    $menu->addChild('Content', '@sympal_content_types_index', array('label' => 'Content'));
    $menu->addChild('Site Administration', '@sympal_sites_edit?id='.$siteManager->getSite()->getId(), array('label' => 'Site Setup'));
    $menu->addChild('Security', '@sympal_users');
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

    $menu->
      addChild('Signout', '@sympal_signout', array('title' => 'Ctrl+Q', 'confirm' => ''.__('Are you sure you want to signout?').'','label' => 'Signout'));
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
