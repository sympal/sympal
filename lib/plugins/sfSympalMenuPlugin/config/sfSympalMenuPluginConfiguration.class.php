<?php

class sfSympalMenuPluginConfiguration extends sfPluginConfiguration
{
  public static
    $dependencies = array(
      'sfSympalPlugin'
    );

  public function initialize()
  {
    $this->dispatcher->connect('sympal.load_editor', array($this, 'loadEditor'));
    $this->dispatcher->connect('sympal.load_admin_menu', array($this, 'loadAdminMenu'));
  }

  public function loadAdminMenu(sfEvent $event)
  {
    $menu = $event->getSubject();

    $administration = $menu->getChild('Administration');
    $menus = $administration->addChild('Menu Manager', '@sympal_menu_items')
      ->setCredentials(array('ManageMenus'));
  }

  public function loadEditor(sfEvent $event)
  {
    $menuItem = $event['menuItem'];
    $menu = $event->getSubject();
    $content = $event['content'];

    if ($menuItem && $menuItem->exists())
    {
      $menuEditor = $menu->addChild('Menu Actions')
        ->setCredentials(array('ManageMenus'));

      if ($menuItem->isPublished())
      {
        $menuEditor->addChild(image_tag('/sf/sf_admin/images/cancel.png').' Un-Publish Menu Item', '@sympal_unpublish_menu_item?id='.$menuItem['id']);
      } else {
        $menuEditor->addChild(image_tag('/sf/sf_admin/images/tick.png').' Publish Menu Item', '@sympal_publish_menu_item?id='.$menuItem['id']);
      }

      $menuItem = $content->getMenuItem();
      if ($menuItem && $menuItem->exists())
      {
        $menuEditor->addChild(image_tag('/sf/sf_admin/images/edit.png').' Edit Menu Item', '@sympal_content_menu_item?id='.$content['id']);
      } else {
        $menuEditor->addChild(image_tag('/sf/sf_admin/images/add.png').' Add to Menu', '@sympal_content_menu_item?id='.$content['id']);
      }

      $menuEditor->addChild(image_tag('/sf/sf_admin/images/add.png').' Add Child Menu Item', 'sympal_menu_items/ListNew?id='.$menuItem['id']);
      $menuEditor->addChild(image_tag('/sf/sf_admin/images/delete.png').' Delete', '@sympal_menu_items_delete?id='.$menuItem['id']);
    }
  }
}