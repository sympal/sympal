<?php
class sfSympalUserProfilePluginConfiguration extends sfPluginConfiguration
{
  public static
    $dependencies = array(
      'sfSympalPlugin'
    );

  public function initialize()
  {
    $this->dispatcher->connect('sympal.load_settings_form', array($this, 'loadSettings'));
    $this->dispatcher->connect('sympal.load_admin_bar', array($this, 'loadAdminBar'));
  }

  public function loadAdminBar(sfEvent $event)
  {
    $menu = $event['menu'];

    $menu->getChild('Administration')->addChild('UserProfile', '@sympal_content_type_user_profile');
  }

  public function loadSettings(sfEvent $event)
  {
    $form = $event->getSubject();

    // $form->addSetting('UserProfile', 'setting_name', 'Setting Label', 'InputCheckbox', 'Boolean');
  }
}