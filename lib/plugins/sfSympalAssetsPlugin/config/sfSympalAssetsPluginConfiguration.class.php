<?php

/**
 * Plugin configuration for the Assets plugin
 * 
 * @package     sfSympalAssetsPlugin
 * @subpackage  config
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */
class sfSympalAssetsPluginConfiguration extends sfPluginConfiguration
{

  /**
   * Initializes the plugin, connects to events
   */
  public function initialize()
  {
    $this->dispatcher->connect('sympal.load_inline_edit_bar_buttons', array($this, 'loadInlineEditBarButtons'));
    $this->dispatcher->connect('sympal.load_admin_menu', array($this, 'loadAdminMenu'));
    $this->dispatcher->connect('sympal.load_config_form', array($this, 'loadConfigForm'));
  }

  /**
   * Listens to the sympal.load_inline_edit_bar_buttons event and customizes
   * the buttons used for frontend editing
   */
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

  /**
   * Listens to the sympal.load_admin_menu event
   */
  public function loadAdminMenu(sfEvent $event)
  {
    $menu = $event->getSubject();
    $menu->getChild('content')
      ->addChild('Assets', '@sympal_assets');
  }

  /**
   * Listens to the sympal.load_config_form event
   */
  public function loadConfigForm(sfEvent $event)
  {
    $form = $event->getSubject();
  }
}