<?php

class Basesympal_content_rendererActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $sympalContext = sfSympalContext::getInstance();
    $this->renderer = $sympalContext->getActionsRenderer($this);
  }
}