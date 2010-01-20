<?php

class sfSympalMenuPluginConfiguration extends sfPluginConfiguration
{
  public static
    $dependencies = array(
      'sfSympalPlugin'
    );

  public function initialize()
  {
    $this->dispatcher->connect('sympal.load_admin_menu', array($this, 'loadAdminMenu'));
  }

  public function loadAdminMenu(sfEvent $event)
  {
    $event->getSubject()
      ->getChild('Site Administration')
      ->addChild('Menus', '@sympal_menu_items')->setCredentials(array('ManageMenus'));
  }
}