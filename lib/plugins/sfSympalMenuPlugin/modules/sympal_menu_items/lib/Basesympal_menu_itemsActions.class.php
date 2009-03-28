<?php

class Basesympal_menu_itemsActions extends autoSympal_menu_itemsActions
{
  public function executeSitemap()
  {
    $table = Doctrine::getTable('MenuItem');
    $this->menuItem = $table->getForSlug('sitemap');
    $this->roots = $table->getTree()->fetchRoots();
  }

  public function executeView()
  {
    $this->menuItem = $this->getRoute()->getObject();
    $this->redirect($this->menuItem->getItemRoute());
  }

  public function executeEdit(sfWebRequest $request)
  {
    parent::executeEdit($request);
    sfSympalTools::setCurrentMenuItem($this->menu_item);
  }

  protected function addSortQuery($query)
  {
    $query->addOrderBy('root_id, lft');
  }

  public function executeBatch(sfWebRequest $request)
  {
    if ($request->getParameter('batch_action') == 'batchOrder')
    {
      return $this->executeBatchOrder($request);
    }

    parent::executeBatch($request);
  }

  public function executeBatchOrder(sfWebRequest $request)
  {
    $newParent = $request->getParameter('new_parent');

    $ids = array();
    foreach ($newParent as $key => $val)
    {
      $ids[$key] = true;
      if (!empty($val))
      {
        $ids[$val] = true;
      }
    }
    $ids = array_keys($ids);

    $validator = new sfValidatorDoctrineChoiceMany(array('model' => 'MenuItem'));
    try
    {
      // validate ids
      $ids = $validator->clean($ids);

      // the id's validate, now update the menu_item
      $count = 0;
      $flash = '';

      $table = Doctrine::getTable('MenuItem');
      foreach ($newParent as $id => $parentId)
      {
        if (!empty($parentId))
        {
          $node = $table->find($id);
          $parent = $table->find($parentId);

          if (!$parent->getNode()->isDescendantOfOrEqualTo($node))
          {
            $node->getNode()->moveAsFirstChildOf($parent);
            $node->save();

            $count++;

            $flash .= "<br/>Moved '".$node['name']."' under '".$parent['name']."'.";
          }
        }
      }

      if ($count > 0)
      {
        $this->getUser()->setFlash('notice', sprintf("Menu item order updated, moved %s item%s:".$flash, $count, ($count > 1 ? 's' : '')));
      } else {
        $this->getUser()->setFlash('error', "You must at least move one item to update the menu item order");
      }
    }
    catch (sfValidatorError $e)
    {
      $this->getUser()->setFlash('error', 'Cannot update the menu item order, maybe some item are deleted, try again');
    }
     
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

    $this->getUser()->setFlash('notice', 'The item was deleted successfully.');

    $this->redirect('@sympal_menu_items');
  }

  public function executeListNew(sfWebRequest $request)
  {
    $this->executeNew($request);
    $this->form->setDefault('parent_id', $request->getParameter('id'));
    $this->setTemplate('edit');
  }

  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid())
    {
      $this->getUser()->setFlash('notice', $form->getObject()->isNew() ? 'The item was created successfully.' : 'The item was updated successfully.');

      $menuItem = $form->save();

      $this->dispatcher->notify(new sfEvent($this, 'admin.save_object', array('object' => $menuItem)));

      if ($request->hasParameter('_save_and_add'))
      {
        $this->getUser()->setFlash('notice', $this->getUser()->getFlash('notice').' You can add another one below.');
      }
    }
    else
    {
      $this->getUser()->setFlash('error', 'The item has not been saved due to some errors.');
    }
  }
}