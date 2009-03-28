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
  }

  public function loadTools(sfEvent $event)
  {
    $menuItem = $event['menuItem'];
    $menu = $event['menu'];

    if ($menuItem && $menuItem->exists())
    {
      $menuEditor = $menu->addChild('Menu Editor');
      $menuEditor->addChild(image_tag('/sf/sf_admin/images/edit.png').' Edit Menu Item', '@sympal_menu_items_edit?id='.$menuItem['id']);
      $menuEditor->addChild(image_tag('/sf/sf_admin/images/add.png').' Add Child Menu Item', 'sympal_menu_items/ListNew?id='.$menuItem['id']);
      $menuEditor->addChild(image_tag('/sf/sf_admin/images/delete.png').' Delete', '@sympal_menu_items_delete?id='.$menuItem['id']);
    }
  }
}