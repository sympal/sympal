<?php
class sfSympalCommentsPluginConfiguration extends sfPluginConfiguration
{
  public 
    $dependencies = array(
      'sfSympalPlugin',
      'sfSympalRegisterPlugin',
      'sfSympalSecurityPlugin',
      'sfFormExtraPlugin'
    );

  public function initialize()
  {
    $this->dispatcher->connect('sympal.load_settings_form', array($this, 'loadSettings'));
    $this->dispatcher->connect('sympal.load_admin_bar', array($this, 'loadAdminBar'));
  }

  public function loadAdminBar(sfEvent $event)
  {
    $menu = $event['menu'];

    if (sfSympalConfig::get('Comments', 'enabled'))
    {
      $menu->getNode('Administration')->addNode('Comments', '@sympal_comments');
    }
  }

  public function loadSettings(sfEvent $event)
  {
    $form = $event->getSubject();

    $form->addSetting('Comments', 'enabled', 'Enabled', 'InputCheckbox', 'Boolean');
    $form->addSetting('Comments', 'enable_recaptcha', 'Enable Recaptcha', 'InputCheckbox', 'Boolean');
    $form->addSetting('Comments', 'recaptcha_public_key', 'Recaptcha Public Key');
    $form->addSetting('Comments', 'recaptcha_private_key', 'Recaptcha Private Key');
    $form->addSetting('Comments', 'requires_auth', 'Commenting Requires Authentication', 'InputCheckbox', 'Boolean');
  }
}