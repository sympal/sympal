<?php

// testing module for things related to themes
class themeActions extends sfActions
{
  public function executeDefault(sfWebRequest $request)
  {
    $this->setTemplate('index');
  }

  public function executeSetTestTheme(sfWebRequest $request)
  {
    $this->loadTheme('test');
    $this->setTemplate('index');
  }

  public function executeSetSiteTheme(sfWebRequest $request)
  {
    $this->loadSiteTheme();
    $this->setTemplate('index');
  }
}