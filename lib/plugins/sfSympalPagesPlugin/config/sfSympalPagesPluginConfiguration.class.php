<?php
class sfSympalPagesPluginConfiguration extends sfPluginConfiguration
{
  public 
    $dependencies = array(
      'sfSympalPlugin',
      'sfSympalI18nPlugin'
    );

  public function initialize()
  {
    $this->dispatcher->connect('sympal.load_settings_form', array($this, 'loadSettings'));
  }

  public function loadSettings(sfEvent $event)
  {
    $form = $event->getSubject();

    $form->addSetting('Page', 'enable_comments', 'Enable Comments', 'InputCheckbox', 'Boolean');
  }
}