<?php

class sfSympalPluginManagerPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    $this->dispatcher->connect('sympal.load_admin_menu', array($this, 'loadAdminMenu'));

    // Connect to the sympal.load_config_form evnet
    $this->dispatcher->connect('sympal.load_config_form', array($this, 'loadConfigForm'));
  }

  public function loadAdminMenu(sfEvent $event)
  {
    $menu = $event->getSubject();

    $administration = $menu->getChild('administration');
    $administration->addChild('Plugin Manager', '@sympal_plugin_manager')
      ->setCredentials(array('ManagePlugins'));
  }

  /**
   * Listens to sympal.load_config_form to load the configuration form
   */
  public function loadConfigForm(sfEvent $event)
  {
    $form = $event->getSubject();
    
    $form->addSetting('plugin_manager', 'plugin_api_username', 'Username or API Key');
    $form->addSetting('plugin_manager', 'plugin_api_password');
  }
}