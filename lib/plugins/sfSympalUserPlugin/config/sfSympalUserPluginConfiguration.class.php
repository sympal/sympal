<?php

/**
 * sfSympalUserPlugin configuration.
 * 
 * @package     sfSympalUserPlugin
 * @subpackage  config
 * @author      Your name here
 * @version     SVN: $Id: PluginConfiguration.class.php 12628 2008-11-04 14:43:36Z Kris.Wallsmith $
 */
class sfSympalUserPluginConfiguration extends sfPluginConfiguration
{
  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    $this->dispatcher->connect('sympal.load_admin_menu', array($this, 'loadAdminMenu'));
  }

  public function loadAdminMenu(sfEvent $event)
  {
    $menu = $event->getSubject();

    $security = $menu->getChild('Security')
      ->setCredentials(array(array('ManageUsers', 'ManageGroups', 'ManagePermissions')));

    $security->addChild('Users', '@sympal_users')
      ->setCredentials(array('ManageUsers'));

    $security->addChild('Groups', '@sympal_groups')
      ->setCredentials(array('ManageGroups'));

    $security->addChild('Permissions', '@sympal_permissions')
      ->setCredentials(array('ManagePermissions'));
  }
}
