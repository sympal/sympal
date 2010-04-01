<?php

class sfSympalMenuPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    $this->dispatcher->connect('sympal.load_admin_menu', array($this, 'loadAdminMenu'));
    $this->dispatcher->connect('sympal.load', array($this, 'bootstrap'));
  }

  public function loadAdminMenu(sfEvent $event)
  {
    $event->getSubject()
      ->getChild('Site Administration')
      ->addChild('Menus', '@sympal_menu_items')->setCredentials(array('ManageMenus'));
  }

  /**
   * Listens to the sympal.load event
   */
  public function bootstrap(sfEvent $event)
  {
    $helpers = array(
      'SympalMenu',
    );
    $event->getSubject()->getApplicationConfiguration()->loadHelpers($helpers);
    
    // Listen to sfSympalContent's change_content event
    $this->dispatcher->connect('sympal.content.set_content', array(
      $event->getSubject()->getService('menu_manager'),
      'listenContentSetContent'
    ));
  }
}