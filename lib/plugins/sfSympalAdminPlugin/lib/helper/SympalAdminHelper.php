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

    $menu->addChild('content', '@sympal_content_types_index', array('label' => 'Content'));
    $menu->getChild('site_administration', '@sympal_sites_edit?id='.$siteManager->getSite()->getId(), array('label' => 'Site Setup'));
    

    sfApplicationConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($menu, 'sympal.load_admin_menu'));

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
