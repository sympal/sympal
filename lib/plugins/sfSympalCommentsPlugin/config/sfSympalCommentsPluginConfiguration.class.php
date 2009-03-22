<?php
class sfSympalCommentsPluginConfiguration extends sfPluginConfiguration
{
  public 
    $dependencies = array(
      'sfSympalPlugin',
      'sfSympalRegisterPlugin',
      'sfSympalSecurityPlugin',
      'sfSympalUserProfilePlugin',
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
      $commentTable = Doctrine::getTable('Comment')->getNumPending();
      $menu->getNode('Administration')->addNode('Comments ('.$commentTable.')', '@sympal_comments');
    }
  }

  public function loadSettings(sfEvent $event)
  {
    $form = $event->getSubject();

    $entityTypes = Doctrine::getTable('EntityType')->findAll();
    foreach ($entityTypes as $entityType)
    {
      $form->addSetting($entityType['name'], 'enable_comments', 'Enable Comments', 'InputCheckbox', 'Boolean');
    }

    $form->addSetting('Comments', 'default_status', 'Default Status');
    $form->addSetting('Comments', 'enabled', 'Enabled', 'InputCheckbox', 'Boolean');
    $form->addSetting('Comments', 'enable_recaptcha', 'Enable Recaptcha', 'InputCheckbox', 'Boolean');
    $form->addSetting('Comments', 'recaptcha_public_key', 'Recaptcha Public Key');
    $form->addSetting('Comments', 'recaptcha_private_key', 'Recaptcha Private Key');
    $form->addSetting('Comments', 'requires_auth', 'Commenting Requires Authentication', 'InputCheckbox', 'Boolean');
  }
}