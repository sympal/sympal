<?php

class Basesympal_editorActions extends sfActions
{
  public function executePublish_content(sfWebRequest $request)
  {
    $content = $this->getRoute()->getObject()->publish();
    
    $this->getUser()->setFlash('notice', 'Content published successfully!');
    $this->redirect($request->getReferer());
  }

  public function executeUnpublish_content(sfWebRequest $request)
  {
    $content = $this->getRoute()->getObject()->unpublish();
    
    $this->getUser()->setFlash('notice', 'Content un-published successfully!');
    $this->redirect($request->getReferer());
  }
}