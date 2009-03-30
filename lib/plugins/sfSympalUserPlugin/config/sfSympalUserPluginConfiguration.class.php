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
    $this->dispatcher->connect('sympal.load_admin_bar', array($this, 'loadAdminBar'));
  }

  public function loadAdminBar(sfEvent $event)
  {
    $menu = $event['menu'];

    $security = $menu->getChild('Security');
    $security->addChild('Users', '@sympal_users');
    $security->addChild('Groups', '@sympal_groups');
    $security->addChild('Permissions', '@sympal_permissions');
  }
}
