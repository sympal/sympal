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
      $_SERVER['PATH_INFO'],
      sfSympalContext::getInstance()->getSite()->getId()
    );
  }

  public function redirect()
  {
    if ($redirect = $this->getRedirect())
    {
      $params = str_replace($_SERVER['SCRIPT_NAME'].$path, null, $_SERVER['REQUEST_URI']);
      $params = $params[0] == '?' ? substr($params, 1) : $params;
      $redirectTo = $redirect->getDestination();
      $redirectTo = $redirectTo.(strpos($redirectTo, '?') !== false ? '&' : '?').$params;
      $this->_actions->redirect($redirectTo);
    }
  }
}