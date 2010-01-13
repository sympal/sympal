<?php

class sfSympalAssetsPluginConfiguration extends sfSympalPluginConfiguration
{
  public function initialize()
  {
    $this->dispatcher->connect('sympal.load_admin_menu', array($this, 'loadAdminMenu'));
    $this->dispatcher->connect('sympal.load_config_form', array($this, 'loadConfigForm'));
    $this->dispatcher->connect('sympal.content_renderer.filter_slot_content', array('sfSympalAssetReplacer', 'listenToFilterSlotContent'));
  }

  public function loadAdminMenu(sfEvent $event)
  {
    $menu = $event->getSubject();
    $menu->getChild('Content')
      ->addChild('Assets', '@sympal_assets');
  }

  public function loadConfigForm(sfEvent $event)
  {
    $form = $event->getSubject();

  }
}