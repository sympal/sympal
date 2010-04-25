<?php

/**
 * Plguin configuration for sfSympalUpgradePlugin
 * 
 * @package     sfSympalUpgradePlugin
 * @subpackage  config
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */

class sfSympalUpgradePluginConfiguration extends sfPluginConfiguration
{

  /**
   * Configures the plugin
   */
  public function initialize()
  {
    // Connect to the sympal.load_admin_menu event
    $this->dispatcher->connect('sympal.load_admin_menu', array($this, 'setupAdminMenu'));
  }

  /**
   * Listens to the sympal.load_admin_menu to configure the admin menu
   */
  public function setupAdminMenu(sfEvent $event)
  {
    $menu = $event->getSubject();
    $administration = $menu->getChild('administration');
    
    $administration->addChild('Check for Updates', '@sympal_check_for_updates')
      ->setCredentials(array('UpdateManager'));
  }
}