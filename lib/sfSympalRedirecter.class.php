<?php

class sfSympalRedirecter
{
  private
    $_actions,
    $_redirect,
    $_destinationRoute;

  public function __construct(sfActions $actions)
  {
    $this->_actions = $actions;
    $this->_redirect = Doctrine_Core::getTable('sfSympalRedirect')->find(
      $this->_actions->getRequest()->getParameter('id')
    );
  }

  public function redirect()
  {
    $this->_actions->redirect($this->_getUrlToRedirectTo(), 301);
  }

  private function _getDestinationRoute()
  {
    if (!$this->_destinationRoute)
    {
      if ($this->_redirect->isDestinationRoute())
      {
        $routes = $this->_actions->getContext()->getRouting()->getRoutes();
        $this->_destinationRoute = $routes[substr($this->_redirect->getDestination(), 1)];
      } else {
        $this->_destinationRoute = new sfRoute($this->_redirect->destination);
      }
    }
    return $this->_destinationRoute;
  }

  private function _getDestinationParameters()
  {
    $parameters = array();
    foreach (array_keys($this->_getDestinationRoute()->getRequirements()) as $name)
    {
      $parameters[$name] = $this->_actions->getRequest()->getParameter($name);
    }
    return $parameters;
  }

  private function _getUrlToRedirectTo()
  {
    switch ($this->_redirect->getDestinationType())
    {
      case 'url':
        $destination = $this->_redirect->getDestination();
      break;

      case 'route':
        $destination = $this->_actions->generateUrl(substr($this->_redirect->getDestination(), 1), $this->_getDestinationParameters());
      break;

      case 'path':
        $destination = $this->_actions->getRequest()->getPathInfoPrefix().$this->_getDestinationRoute()->generate($this->_getDestinationParameters());
      break;

      case 'content':
        $destination = $this->_redirect->getContent()->getUrl();
      break;
    }
    return $destination;
  }
}