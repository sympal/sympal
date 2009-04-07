<?php

class sfSympalMenuPluginConfiguration extends sfPluginConfiguration
{
  public static
    $dependencies = array(
      'sfSympalPlugin'
    );

  public function initialize()
  {
    $this->dispatcher->connect('sympal.load_tools', array($this, 'loadTools'));
    $this->dispatcher->connect('sympal.load_admin_bar', array($this, 'loadAdminBar'));
  }

  public function loadAdminBar(sfEvent $event)
  {
    $menu = $event['menu'];

    $administration = $menu->getChild('Administration');
    $administration->addChild('Menu Manager', '@sympal_menu_manager')
      ->setCredentials(array('ManageMenus'));
  }

  public function loadTools(sfEvent $event)
  {
    $menuItem = $event['menuItem'];
    $menu = $event['menu'];

    if ($menuItem && $menuItem->exists())
    {
      $menuEditor = $menu->addChild('Menu Editor')
        ->setCredentials(array('ManageMenus'));

      $menuEditor->addChild(image_tag('/sf/sf_admin/images/edit.png').' Edit Menu Item', '@sympal_menu_items_edit?id='.$menuItem['id']);
      $menuEditor->addChild(image_tag('/sf/sf_admin/images/add.png').' Add Child Menu Item', 'sympal_menu_items/ListNew?id='.$menuItem['id']);
      $menuEditor->addChild(image_tag('/sf/sf_admin/images/delete.png').' Delete', '@sympal_menu_items_delete?id='.$menuItem['id']);
    }
  }
}