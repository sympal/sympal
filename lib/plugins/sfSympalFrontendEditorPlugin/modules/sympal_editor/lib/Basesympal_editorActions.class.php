<?php

class Basesympal_editorActions extends sfActions
{
  public function executePublish_content(sfWebRequest $request)
  {
    $this->getRoute()->getObject()->publish();
    
    $this->getUser()->setFlash('notice', 'Content published successfully!');
    $this->redirect($request->getReferer());
  }

  public function executeUnpublish_content(sfWebRequest $request)
  {
    $this->getRoute()->getObject()->unpublish();
    
    $this->getUser()->setFlash('notice', 'Content un-published successfully!');
    $this->redirect($request->getReferer());
  }

  public function executeLinks(sfWebRequest $request)
  {
    $this->content = Doctrine_Core::getTable('sfSympalContent')
      ->createQuery('c')
      ->leftJoin('c.MenuItem m')
      ->where('c.site_id = ?', $this->getSympalContext()->getSite()->getId())
      ->orderBy('m.root_id, m.lft ASC')
      ->execute();
  }
}