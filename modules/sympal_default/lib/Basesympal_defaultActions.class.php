<?php

class Basesympal_defaultActions extends sfActions
{
  public function preExecute()
  {
    sfSympalToolkit::loadDefaultLayout();
  }

  public function executeAsk_confirmation(sfWebRequest $request)
  {
    $this->url = $request->getUri();
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