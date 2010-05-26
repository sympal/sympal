<?php

/**
 * Base actions class for handling menu administration
 * 
 * @package     sfSympalMenuPlugin
 * @subpackage  actions
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class Basesympal_menu_itemsActions extends autoSympal_menu_itemsActions
{
  public function preExecute()
  {
    parent::preExecute();

    $this->getContext()->getEventDispatcher()->connect('admin.build_query', array($this, 'listenToAdminBuildQuery'));
  }

  public function listenToAdminBuildQuery(sfEvent $event, Doctrine_Query $query)
  {
    $query->andWhere('site_id = ?', sfSympalContext::getInstance()->getService('site_manager')->getSite()->getId());

    return $query;
  }

  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid())
    {
      $this->getUser()->setFlash('notice', $form->getObject()->isNew() ? 'The item was created successfully.' : 'The item was updated successfully.');

      $form->getObject()->setSite($this->getSympalContext()->getSite());

      $tree = $form->save();

      $this->clearMenuCache();

      $this->dispatcher->notify(new sfEvent($this, 'admin.save_object', array('object' => $tree)));

      if ($request->hasParameter('_save_and_add'))
      {
        $this->getUser()->setFlash('notice', $this->getUser()->getFlash('notice').' You can add another one below.');

        $this->redirect('@sympal_menu_items_new');
      }
      else
      {
        $this->redirect('@sympal_menu_items_edit?id='.$tree['id']);
      }
    }
    else
    {
      $this->getUser()->setFlash('error', 'The item has not been saved due to some errors.');
    }
  }

  protected function _getMenuItem(sfWebRequest $request)
  {
    $q = Doctrine_Core::getTable('sfSympalMenuItem')
      ->createQuery('m')
      ->leftJoin('m.Groups g')
      ->where('m.id = ?', $request->getParameter('id'));

    if (sfSympalConfig::isI18nEnabled('sfSympalMenuItem'))
    {
      $q->leftJoin('m.Translation mt');
    }

    $menuItem = $q->fetchOne();
    $this->forward404Unless($menuItem);

    return $menuItem;
  }

  protected function _publishMenuItem(sfSympalMenuItem $menuItem, $publish = true)
  {
    $func = $publish ? 'publish':'unpublish';

    return $menuItem->$func();
  }

  public function executeBatchPublish(sfWebRequest $request)
  {
    $ids = $request->getParameter('ids');

    $menuItems = Doctrine_Core::getTable('sfSympalMenuItem')
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

    $menuItems = Doctrine_Core::getTable('sfSympalMenuItem')
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

    $this->getUser()->setFlash('notice', 'Menu item published successfully!');
    $this->redirect($request->getReferer());
  }

  public function executeUnpublish(sfWebRequest $request)
  {
    $menuItem = $this->_getMenuItem($request);
    $this->_publishMenuItem($menuItem, false);

    $this->getUser()->setFlash('notice', 'Menu item unpublished successfully!');
    $this->redirect($request->getReferer());
  }

  public function executeEdit(sfWebRequest $request)
  {
    $this->sf_sympal_menu_item = $this->_getMenuItem($request);
    $this->form = $this->configuration->getForm($this->sf_sympal_menu_item);

    if ($this->sf_sympal_menu_item)
    {
      $this->getSympalContext()->getService('menu_manager')->setCurrentMenuItem($this->sf_sympal_menu_item);
    }
  }

  protected function executeBatchDelete(sfWebRequest $request)
  {
    $ids = $request->getParameter('ids');

    $records = Doctrine_Query::create()
      ->from('sfSympalMenuItem')
      ->whereIn('id', $ids)
      ->execute();

    foreach ($records as $record)
    {
      if ($record->getNode()->isValidNode())
      {
        $record->getNode()->delete();
      }
      else
      {
        $record->delete();
      }
    }

    $this->clearMenuCache();

    $this->getUser()->setFlash('notice', 'The selected items have been deleted successfully.');
    $this->redirect('@sympal_menu_items');
  }

  public function executeDelete(sfWebRequest $request)
  {
    $request->checkCSRFProtection();

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

    $this->clearMenuCache();

    $this->getUser()->setFlash('notice', 'The item was deleted successfully.');

    $this->redirect('@sympal_menu_items');
  }

  protected function addSortQuery($query)
  {
    //don't allow sorting; always sort by tree and lft
    $query->addOrderBy('root_id, lft', 'asc');
  }

  public function executeBatch(sfWebRequest $request)
  {
    if ('batchOrder' == $request->getParameter('batch_action'))
    {
      return $this->executeBatchOrder($request);
    }
    
    parent::executeBatch($request);
  }

  public function executeListLeft(sfWebRequest $request)
  {
    $id = $this->getRequestParameter('id');
    $record = Doctrine::getTable('sfSympalMenuItem')->find($id);
    $parent = $record->getNode()->getParent();
    if ($parent != '')
    {
      $record->getNode()->moveAsNextSiblingOf($parent);
      $this->redirect('@sympal_menu_items');
    }
    else
    {
      $this->getUser()->setFlash('error', 'There is no parent for the entry '.$record);
      $this->redirect('@sympal_menu_items');
    }
  }
  
  public function executeListRight(sfWebRequest $request)
  {
    $id = $this->getRequestParameter('id');
    $record = Doctrine::getTable('sfSympalMenuItem')->find($id);
    $prev = $record->getNode()->getPrevSibling();
    if ($prev != '')
    {
      $record->getNode()->moveAsFirstChildOf($prev);
      $this->redirect('@sympal_menu_items');
    }
    else
    {
      $this->getUser()->setFlash('error', 'There is no previous sibling for the entry '.$record);
      $this->redirect('@sympal_menu_items');
    }
  }
  
  public function executeListDown(sfWebRequest $request)
  {
    $id = $this->getRequestParameter('id');
    $record = Doctrine::getTable('sfSympalMenuItem')->find($id);
    $next = $record->getNode()->getNextSibling();
    if ($next != '')
    {
      $record->getNode()->moveAsNextSiblingOf($next);
      $this->redirect('@sympal_menu_items');
    }
    else
    {
      $this->getUser()->setFlash('error', 'There is no next sibling for the entry '.$record);
      $this->redirect('@sympal_menu_items');
    }
  }
  
  public function executeListUp(sfWebRequest $request)
  {
    $id = $this->getRequestParameter('id');
    $record = Doctrine::getTable('sfSympalMenuItem')->find($id);
    $prev = $record->getNode()->getPrevSibling();
    if($prev != '')
    { 
      $record->getNode()->moveAsPrevSiblingOf($prev);
      $this->redirect('@sympal_menu_items');
    }
    else
    {
      $this->getUser()->setFlash('error', 'There is no previous sibling for the entry '.$record);
      $this->redirect('@sympal_menu_items');
    }
  }

  public function executeBatchOrder(sfWebRequest $request)
  {
    $newparent = $request->getParameter('newparent');

    //manually validate newparent parameter
    
    //make list of all ids
    $ids = array();
    foreach ($newparent as $key => $val)
    {
      $ids[$key] = true;
      if (!empty($val))
        $ids[$val] = true;
    }
    $ids = array_keys($ids);
    
    //validate if all id's exist
    $validator = new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalMenuItem'));
    try
    {
      // validate ids
      $ids = $validator->clean($ids);
 
      // the id's validate, now update the tree
      $count = 0;
      $flash = "";
 
      foreach ($newparent as $id => $parentId)
      {
        if (!empty($parentId))
        {
          $node = Doctrine::getTable('sfSympalMenuItem')->find($id);
          $parent = Doctrine::getTable('sfSympalMenuItem')->find($parentId);
          
          if (!$parent->getNode()->isDescendantOfOrEqualTo($node))
          {
            $node->getNode()->moveAsFirstChildOf($parent);
            $node->save();
 
            $count++;
 
            $flash .= " Moved '".$node['name']."' under '".$parent['name']."'.";
          }
        }
      }
 
      if ($count > 0)
      {
        $this->getUser()->setFlash('notice', sprintf("Tree order updated, moved %s item%s:".$flash, $count, ($count > 1 ? 's' : '')));
      }
      else
      {
        $this->getUser()->setFlash('error', "You must at least move one item to update the tree order");
      }
    }
    catch (sfValidatorError $e)
    {
      $this->getUser()->setFlash('error', 'Cannot update the tree order, maybe some item are deleted, try again');
    }
     
    $this->redirect('@sympal_menu_items');
  }
}
