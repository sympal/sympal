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
    return Doctrine_Core::getTable('sfSympalRedirect')
      ->createQuery('r')
      ->leftJoin('r.Content c')
      ->leftJoin('c.Type t')
      ->where('r.source = ?', $this->_actions->getRequest()->getPathInfo())
      ->andWhere('r.site_id = ?', $this->_actions->getSympalContext()->getSite()->getId())
      ->fetchOne();
  }

  public function redirect()
  {
    if ($redirect = $this->getRedirect())
    {
      if ($destination = $redirect->getDestination())
      {
        // If the destination is not a url or a symfony route then prefix
        // it with the current requests pathinfo prefix
        if ($destination[0] != '@' && substr($destination, 0, 3) != 'http')
        {
          $destination = trim($destination, '/');
          $destination = $this->_actions->getRequest()->getPathInfoPrefix().'/'.$destination;
        }
      } else {
        $destination = $redirect->getContent()->getUrl();
      }
      $this->_actions->redirect($destination);
    }
  }
}