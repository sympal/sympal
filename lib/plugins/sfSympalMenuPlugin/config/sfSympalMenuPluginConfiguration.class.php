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
    $menus = $administration->addChild('Menu Manager', '@sympal_menu_manager')
      ->setCredentials(array('ManageMenus'));

    $table = Doctrine_Core::getTable('MenuItem');
    $roots = $table->getTree()->fetchRoots();
    foreach ($roots as $root)
    {
      $menus->addChild($root['name'], '@sympal_menu_manager_tree?slug='.$root['slug']);
    }
  }

  public function loadTools(sfEvent $event)
  {
    $menuItem = $event['menuItem'];
    $menu = $event['menu'];

    if ($menuItem && $menuItem->exists())
    {
      $menuEditor = $menu->addChild('Menu Editor')
        ->setCredentials(array('ManageMenus'));

      if ($menuItem['is_published'])
      {
        $menuEditor->addChild(image_tag('/sf/sf_admin/images/cancel.png').' Un-Publish Menu Item', '@sympal_unpublish_menu_item?id='.$menuItem['id']);
      } else {
        $menuEditor->addChild(image_tag('/sf/sf_admin/images/tick.png').' Publish Menu Item', '@sympal_publish_menu_item?id='.$menuItem['id']);
      }

      $menuEditor->addChild(image_tag('/sf/sf_admin/images/edit.png').' Edit Menu Item', '@sympal_menu_items_edit?id='.$menuItem['id']);
      $menuEditor->addChild(image_tag('/sf/sf_admin/images/add.png').' Add Child Menu Item', 'sympal_menu_items/ListNew?id='.$menuItem['id']);
      $menuEditor->addChild(image_tag('/sf/sf_admin/images/delete.png').' Delete', '@sympal_menu_items_delete?id='.$menuItem['id']);
    }
  }
}