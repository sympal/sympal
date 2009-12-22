<?php
class sfSympalPluginManagerPluginConfiguration extends sfPluginConfiguration
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
    $menu = $event->getSubject();

    $administration = $menu->getChild('Administration');
    $administration->addChild('Plugin Manager', '@sympal_plugin_manager')
      ->setCredentials(array('ManagePlugins'));
  }
}