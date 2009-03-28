<?php

class Basesympal_defaultActions extends sfActions
{
  public function executeAsk_confirmation(sfWebRequest $request)
  {
    sfSympalTools::loadDefaultLayout();

    $this->url = $request->getRelativeUrlRoot().$request->getPathInfo();
    $this->title = $request->getAttribute('title');
    $this->message = $request->getAttribute('message');
  }

  public function executeSecure()
  {
  }

  public function executeError404()
  {
  }

  public function executeDisabled()
  {
    
  }
}