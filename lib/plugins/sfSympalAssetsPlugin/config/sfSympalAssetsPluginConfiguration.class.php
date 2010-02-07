<?php

class sfSympalAssetsPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    $this->dispatcher->connect('sympal.load_admin_menu', array($this, 'loadAdminMenu'));
    $this->dispatcher->connect('sympal.load_config_form', array($this, 'loadConfigForm'));
    $this->dispatcher->connect('sympal.load_inline_edit_bar_buttons', array($this, 'loadInlineEditBarButtons'));
  }

  public function loadInlineEditBarButtons(sfEvent $event)
  {
    if ($event['content']->getEditableSlotsExistOnPage())
    {
      $menu = $event->getSubject();
      $menu->
        addChild('Assets', '@sympal_assets_select')->
        isEditModeButton(true)->
        setShortcut('Ctrl+Shift+A')->
        setInputClass('toggle_sympal_assets')->
        setCredentials('InsertAssets')
      ;
    }
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