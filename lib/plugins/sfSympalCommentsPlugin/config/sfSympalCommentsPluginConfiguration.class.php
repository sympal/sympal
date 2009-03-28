<?php
class sfSympalCommentsPluginConfiguration extends sfPluginConfiguration
{
  public static
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

    if (sfSympalConfig::get('sfSympalCommentsPlugin', 'enabled'))
    {
      $commentTable = Doctrine::getTable('Comment')->getNumPending();
      $menu->getChild('Administration')->addChild('Comments ('.$commentTable.')', '@sympal_comments');
    }
  }

  public function loadSettings(sfEvent $event)
  {
    $form = $event->getSubject();

    $contentTypes = Doctrine::getTable('ContentType')->findAll();
    foreach ($contentTypes as $contentType)
    {
      $form->addSetting($contentType['name'], 'enable_comments', 'Enable Comments', 'InputCheckbox', 'Boolean');
    }

    $form->addSetting('Comments', 'default_status', 'Default Status');
    $form->addSetting('Comments', 'enabled', 'Enabled', 'InputCheckbox', 'Boolean');
    $form->addSetting('Comments', 'enable_recaptcha', 'Enable Recaptcha', 'InputCheckbox', 'Boolean');
    $form->addSetting('Comments', 'requires_auth', 'Commenting Requires Authentication', 'InputCheckbox', 'Boolean');
  }
}