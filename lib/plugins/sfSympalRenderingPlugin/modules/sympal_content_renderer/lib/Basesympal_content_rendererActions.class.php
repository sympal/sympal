<?php

class Basesympal_content_rendererActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $this->renderer = $this->getSympalContentActionLoader()->loadContentRenderer();
  }
}