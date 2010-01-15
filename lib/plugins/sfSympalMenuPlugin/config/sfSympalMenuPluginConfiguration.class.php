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
    $event->getSubject()
      ->getChild('Site Administration')
      ->addChild('Menus', '@sympal_menu_items')->setCredentials(array('ManageMenus'));
  }

  public function loadEditor(sfEvent $event)
  {
    $menuItem = $event['menuItem'];
    $menu = $event->getSubject();
    $content = $event['content'];

    $menuEditor = $menu->addChild('Menu Actions')
      ->setCredentials(array('ManageMenus'));

    if ($menuItem && $menuItem->exists())
    {
      if ($menuItem->getIsPublished())
      {
        $menuEditor->addChild(image_tag('/sf/sf_admin/images/cancel.png').' Un-Publish Menu Item', '@sympal_unpublish_menu_item?id='.$menuItem['id']);
      } else {
        $menuEditor->addChild(image_tag('/sf/sf_admin/images/tick.png').' Publish Menu Item', '@sympal_publish_menu_item?id='.$menuItem['id']);
      }

      $menuEditor->addChild(image_tag('/sf/sf_admin/images/edit.png').' Edit Menu Item', '@sympal_content_menu_item?id='.$content['id']);

      $menuEditor->addChild(image_tag('/sf/sf_admin/images/delete.png').' Delete', '@sympal_menu_items_delete?id='.$menuItem['id']);
    } else {
      $menuEditor->addChild(image_tag('/sf/sf_admin/images/add.png').' Add to Menu', '@sympal_content_menu_item?id='.$content['id']);
    }
  }
}