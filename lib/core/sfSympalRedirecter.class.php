<?php

class sfSympalRedirecter
{
  private $_actions;

  public function __construct(sfActions $actions)
  {
    $this->_actions = $actions;
  }

  public function getRedirect()
  {
    return Doctrine_Core::getTable('sfSympalRedirect')->findOneBySourceAndSiteId(
      $this->_actions->getRequest()->getPathInfo(),
      sfSympalContext::getInstance()->getSite()->getId()
    );
  }

  public function redirect()
  {
    if ($redirect = $this->getRedirect())
    {
      $destination = $redirect->getDestination();

      // If the destination is not a url or a symfony route then prefix
      // it with the current requests pathinfo prefix
      if ($destination[0] != '@' && substr($destination, 0, 3) != 'http')
      {
        $destination = trim($destination, '/');
        $destination = $this->_actions->getRequest()->getPathInfoPrefix().'/'.$destination;
      }
      $this->_actions->redirect($destination);
    }
  }
}