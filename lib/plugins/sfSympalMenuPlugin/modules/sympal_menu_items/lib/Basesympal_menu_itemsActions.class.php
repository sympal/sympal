<?php

class Basesympal_menu_itemsActions extends autoSympal_menu_itemsActions
{
  public function preExecute()
  {
    parent::preExecute();

    $this->useAdminTheme();
  }

  protected function _getMenuItem(sfWebRequest $request)
  {
    $q = Doctrine_Core::getTable('MenuItem')
      ->createQuery('m')
      ->where('m.id = ?', $request->getParameter('id'));

    $menuItem = $q->fetchOne();
    $this->forward404Unless($menuItem);
    return $menuItem;
  }

  protected function _publishMenuItem(MenuItem $menuItem, $publish = true)
  {
    $func = $publish ? 'publish':'unpublish';
    return $menuItem->$func();
  }

  public function executeBatchPublish(sfWebRequest $request)
  {
    $ids = $request->getParameter('ids');

    $menuItems = Doctrine_Core::getTable('MenuItem')
      ->createQuery('m')
      ->whereIn('m.id', $ids)
      ->execute();

    foreach ($menuItems as $menuItem)
    {
      $this->_publishMenuItem($menuItem, true);
    }
  }

  public function executeBatchUnpublish(sfWebRequest $request)
  {
    $ids = $request->getParameter('ids');

    $menuItems = Doctrine_Core::getTable('MenuItem')
      ->createQuery('m')
      ->whereIn('m.id', $ids)
      ->execute();

    foreach ($menuItems as $menuItem)
    {
      $this->_publishMenuItem($menuItem, false);
    }
  }

  public function executePublish(sfWebRequest $request)
  {
    $menuItem = $this->_getMenuItem($request);
    $publish = $this->_publishMenuItem($menuItem, true);

    $msg = $publish ? 'Menu item published successfully!':'Menu item unpublished successfully!';
    $this->getUser()->setFlash('notice', $msg);
    $this->redirect($request->getReferer());
  }

  public function executeUnpublish(sfWebRequest $request)
  {
    $menuItem = $this->_getMenuItem($request);
    $this->_publishMenuItem($menuItem, false);
  }

  public function executeSitemap()
  {
    $table = Doctrine_Core::getTable('MenuItem');
    $this->menuItem = $table->getForSlug('sitemap');
    $this->roots = $table->getTree()->fetchRoots();
  }

  public function executeManager(sfWebRequest $request)
  {
    $q = Doctrine_Core::getTable('MenuItem')
      ->createQuery()
      ->andWhere('site_id = ?', sfSympalContext::getInstance()->getSite()->getId());

    if ($request->hasParameter('slug'))
    {
      $q->andWhere('slug = ?', $request->getParameter('slug'));
    } else {
      $q->andWhere('is_primary = ?', true);
    }

    $this->menuItem = $q->fetchOne();
    $table = Doctrine_Core::getTable('MenuItem');
    $this->roots = $table->getTree()->fetchRoots();

    $this->dispatcher->connect('sympal.load_side_bar', array($this, 'loadSideBar'));
  }

  public function loadSideBar(sfEvent $event)
  {
    $menu = $event['menu'];

    foreach ($this->roots as $root)
    {
      $menu[$root['slug']]
        ->setLabel('Manage '.$root['name'])
        ->setRoute('@sympal_menu_manager_tree?slug='.$root['slug']);
    }
    $menu['Create New Menu']->setRoute('@sympal_menu_items_new');
  }

  public function executeManager_move(sfWebRequest $request)
  {
    $this->menuItem = $this->getRoute()->getObject();
    $moveId = $request->getParameter('move_id');
    $toId = $request->getParameter('to_id');

    $table = Doctrine_Core::getTable('MenuItem');
    $move = $table->find($moveId);
    $to = $table->find($toId);

    $moveAction = strtolower($request->getParameter('move_action'));
    switch ($moveAction)
    {
      case 'before':
        $func = 'moveAsPrevSiblingOf';
      break;

      case 'after':
        $func = 'moveAsNextSiblingOf';
      break;

      case 'under':
        $func = 'moveAsLastChildOf';
      break;
    }

    $move->getNode()->$func($to);

    $manager = sfSympalMenuSiteManager::getInstance();
    $manager->refresh();

    $this->setTemplate('refresh');
  }

  public function executeManager_delete_node(sfWebRequest $request)
  {
    $menuItem = $this->getRoute()->getObject();
    if ($menuItem->getLevel() == 0)
    {
      $this->redirect('@sympal_menu_manager_tree_delete?slug='.$menuItem['slug']);
    }

    if ($this->askConfirmation('Are you sure?', 'Are you sure you wish to delete this menu item? This action is irreversible!'))
    {
      if ($menuItem->getNode()->isValidNode())
      {
        $menuItem->getNode()->delete();
      }
      else
      {
        $menuItem->delete();
      }

      $this->getUser()->setFlash('notice', 'Menu item was successfully deleted!');
      $this->redirect('@sympal_menu_manager_tree?slug='.$request->getParameter('root_slug'));
    }
  }

  public function executeManager_delete(sfWebRequest $request)
  {
    $menuItem = $this->getRoute()->getObject();
    if ($menuItem->is_primary)
    {
      $this->getUser()->setFlash('error', 'You cannot delete the primary sympal menu!');
      $this->redirect($request->getReferer());
    }
    
    $this->askConfirmation('Are you sure?', 'Are you sure you wish to delete this menu? This action is irreversible!');

    if ($menuItem->getNode()->isValidNode())
    {
      $menuItem->getNode()->delete();
    }
    else
    {
      $menuItem->delete();
    }

    $this->getUser()->setFlash('notice', 'Menu was successfully deleted!');
    $this->redirect('@sympal_menu_items');
  }

  public function executeManager_update(sfWebRequest $request)
  {
    $menuItem = $this->getRoute()->getObject();
    $menuItem->label = $request->getParameter('new_label');
    $menuItem->save();

    $manager = sfSympalMenuSiteManager::getInstance();
    $manager->refresh();

    $this->menuItem = Doctrine_Core::getTable('MenuItem')->findOneBySlug($request->getParameter('root_slug'));

    $this->setTemplate('refresh');
  }

  public function executeManager_add(sfWebRequest $request)
  {
    $menuItem = $this->getRoute()->getObject();

    $new = new MenuItem();
    $new->label = $request->getParameter('new_label');
    $new->name = $new->label;
    $new->getNode()->insertAsLastChildOf($menuItem);

    $manager = sfSympalMenuSiteManager::getInstance();
    $manager->refresh();

    $this->menuItem = Doctrine_Core::getTable('MenuItem')->findOneBySlug($request->getParameter('root_slug'));

    $this->setTemplate('refresh');
  }

  public function executeView()
  {
    $this->menuItem = $this->getRoute()->getObject();
    $this->redirect($this->menuItem->getItemRoute());
  }

  public function executeEdit(sfWebRequest $request)
  {
    parent::executeEdit($request);
    sfSympalToolkit::setCurrentMenuItem($this->menu_item);
  }

  public function executeDelete(sfWebRequest $request)
  {
    $request->checkCSRFProtection();

    $this->askConfirmation('Are you sure?', 'Are you sure you wish to delete this menu item? This action is irreversible!');

    $this->dispatcher->notify(new sfEvent($this, 'admin.delete_object', array('object' => $this->getRoute()->getObject())));

    $object = $this->getRoute()->getObject();
    if ($object->getNode()->isValidNode())
    {
      $object->getNode()->delete();
    }
    else
    {
      $object->delete();
    }

    $this->getUser()->setFlash('notice', 'The item was deleted successfully.');

    $this->redirect('@sympal_menu_items');
  }

  public function executeListNew(sfWebRequest $request)
  {
    $this->executeNew($request);
    $this->form->setDefault('parent_id', $request->getParameter('id'));
    $this->setTemplate('edit');
  }
}