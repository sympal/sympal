<?php
class sfSympalI18nPluginConfiguration extends sfPluginConfiguration
{
  public 
    $dependencies = array(
      'sfSympalPlugin',
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

    if (sfSympalConfig::get('I18n', 'enabled'))
    {
      $i18n = $menu->getNode('Internationalization');
      $i18n->addNode('Languages', '@sympal_languages');
      $i18n->addNode('Translations', '@sympal_translations');
    }
  }

  public function loadSettings(sfEvent $event)
  {
    $form = $event->getSubject();

    $i18n = array();
    $i18n['enabled'] = 'Enabled';
    $i18n['slots'] = 'Slots';
    $i18n['menus'] = 'Menus';

    foreach ($i18n as $name => $label)
    {
      $form->addSetting('I18n', $name, $label, 'InputCheckbox', 'Boolean');
    }
  }
}