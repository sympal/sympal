<?php

class sfSympalAssetsPluginConfiguration extends sfSympalPluginConfiguration
{
  public function initialize()
  {
    $this->dispatcher->connect('sympal.load_admin_menu', array($this, 'loadAdminMenu'));
    $this->dispatcher->connect('sympal.content_renderer.filter_slot_content', array('sfSympalAssetReplacer', 'listenToFilterSlotContent'));
  }

  public function loadAdminMenu(sfEvent $event)
  {
    $menu = $event->getSubject();
    $menu->getChild('Administration')
      ->addChild('Assets Manager', '@sympal_assets');
  }
}