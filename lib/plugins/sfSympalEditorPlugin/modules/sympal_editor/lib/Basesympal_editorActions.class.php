<?php

/**
 * Actions class handling global, frontend-editing actions
 * 
 * @package     sfSympalEditorPlugin
 * @subpackage  actions
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-02-27
 * @version     svn:$Id$ $Author$
 */
class Basesympal_editorActions extends sfActions
{
  /**
   * Handles the publishing of content - handles the request from the
   * frontend publish/unpublish button
   */
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

  /**
   * Handles the publishing of content - handles the request from the
   * frontend publish/unpublish button
   */
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
  
  /**
   * Renders the "Link Browser" used to add link "markup" to your content
   */
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