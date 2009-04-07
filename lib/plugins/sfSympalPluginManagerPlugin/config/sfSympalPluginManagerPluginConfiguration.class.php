<?php
class sfSympalPluginManagerPluginConfiguration extends sfPluginConfiguration
{
  public static
    $dependencies = array(
      'sfSympalPlugin'
    );

  public function initialize()
  {
    $this->dispatcher->connect('sympal.load_admin_bar', array($this, 'loadAdminBar'));
  }

  public function loadAdminBar(sfEvent $event)
  {
    $menu = $event['menu'];

    $administration = $menu->getChild('Administration');
    $administration->addChild('Plugin Manager', '@sympal_plugin_manager')
      ->setCredentials(array('ManagePlugins'));
  }
}