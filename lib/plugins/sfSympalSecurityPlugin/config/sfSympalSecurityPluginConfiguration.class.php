<?php

class sfSympalSecurityPluginConfiguration extends sfPluginConfiguration
{
  public static
    $dependencies = array(
      'sfSympalPlugin',
      'sfDoctrineGuardPlugin'
    );

  public function initialize()
  {
    $this->dispatcher->connect('sympal.load_admin_bar', array($this, 'loadAdminBar'));
  }

  public function loadAdminBar(sfEvent $event)
  {
    $menu = $event['menu'];

    $security = $menu->getChild('Security');
    $security->addChild('Users', '@sf_guard_users');
    $security->addChild('Groups', '@sf_guard_groups');
    $security->addChild('Permissions', '@sf_guard_permissions');
  }
}