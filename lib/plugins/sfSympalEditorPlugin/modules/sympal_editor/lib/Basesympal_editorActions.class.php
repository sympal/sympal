<?php

class Basesympal_editorActions extends sfActions
{
  public function executePublish_content(sfWebRequest $request)
  {
    $this->askConfirmation(
      'Publish Content',
      'Are you sure you want publish this content?'
    );

    $this->getRoute()->getObject()->publish();

    $this->getUser()->setFlash('notice', 'Content published successfully!');
    $this->redirect($request->getParameter('redirect_url'));
  }

  public function executeUnpublish_content(sfWebRequest $request)
  {
    $this->askConfirmation(
      'Un-publish Content',
      'Are you sure you want un-publish this content?'
    );

    $this->getRoute()->getObject()->unpublish();
    
    $this->getUser()->setFlash('notice', 'Content un-published successfully!');
    $this->redirect($request->getParameter('redirect_url'));
  }

  public function executeLinks(sfWebRequest $request)
  {
    $this->contentTypes = Doctrine_Core::getTable('sfSympalContentType')
      ->createQuery('t')
      ->orderBy('t.label ASC')
      ->execute();
    if (!$contentTypeId = $request->getParameter('content_type_id'))
    {
      $this->contentType = Doctrine_Core::getTable('sfSympalContentType')->findOneByName('sfSympalPage');
    } else {
      $this->contentType = Doctrine_Core::getTable('sfSympalContentType')->find($contentTypeId);
    }
    $contentTypeId = $this->contentType->getId();
    $this->content = Doctrine_Core::getTable('sfSympalContent')
      ->getFullTypeQuery($this->contentType->getName(), 'c', $contentTypeId)
      ->orderBy('m.root_id, m.lft ASC')
      ->execute();
  }
  
  /**
   * Ajax action that populates the object choose on the editor toolbar
   */
  public function executeObjects(sfWebRequest $request)
  {
    $this->slotKeys = array_keys(sfSympalConfig::get('content_slot_objects', null, array()));
    
    if (count($this->slotKeys))
    {
      if (!$object_slug = $request->getParameter('slot_key'))
      {
        $this->slotKey = $this->slotKeys[0];
      } else {
        $this->slotKey = $object_slug;
      }
      
      $config = sfSympalConfig::get('content_slot_objects', $this->slotKey);
      $class = $config['class'];
      
      $tbl = Doctrine_Core::getTable($class);
      
      $q = (method_exists($tbl, 'getObjectSlotQuery')) ? $tbl->getObjectSlotQuery() : $tbl->createQuery();
      $this->objects = $q->execute();
    }
    else
    {
      $this->slotKey = false;
    }
  }
}