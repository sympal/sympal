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
}