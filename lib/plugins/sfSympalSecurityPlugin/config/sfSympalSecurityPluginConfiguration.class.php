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

    $security = $menu->getNode('Security');
    $security->addNode('Users', '@sf_guard_users');
    $security->addNode('Groups', '@sf_guard_groups');
    $security->addNode('Permissions', '@sf_guard_permissions');
  }
}