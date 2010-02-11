<?php

/**
 * Class responsible for handling the sfSympalRedirect routes
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalRedirecter
{
  private
    $_actions,
    $_redirect,
    $_destinationRouteObject;

  public function __construct(sfActions $actions)
  {
    $this->_actions = $actions;
    $this->_redirect = Doctrine_Core::getTable('sfSympalRedirect')->find(
      $this->_actions->getRequest()->getParameter('id')
    );
  }

  /**
   * Handle the redirect and go to the redirects destination
   *
   * @return void
   */
  public function redirect()
  {
    $this->_actions->redirect($this->_getUrlToRedirectTo(), 301);
  }

  /**
   * Get the destination route object
   *
   * @return sfRoute $destinationRoute
   */
  private function _getDestinationRouteObject()
  {
    if (!$this->_destinationRouteObject)
    {
      if ($this->_redirect->isDestinationRoute())
      {
        $routes = $this->_actions->getContext()->getRouting()->getRoutes();
        $this->_destinationRouteObject = $routes[substr($this->_redirect->getDestination(), 1)];
      } else {
        $this->_destinationRouteObject = new sfRoute($this->_redirect->destination);
      }
    }
    return $this->_destinationRouteObject;
  }

  /**
   * Get the array of destination parameters
   *
   * @return array $parameters
   */
  private function _getDestinationParameters()
  {
    $parameters = array();
    foreach (array_keys($this->_getDestinationRouteObject()->getRequirements()) as $name)
    {
      $parameters[$name] = $this->_actions->getRequest()->getParameter($name);
    }
    return $parameters;
  }

  /**
   * Get the url to redirect to
   *
   * @return string $destination
   */
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
        $destination = $this->_actions->getRequest()->getPathInfoPrefix().$this->_getDestinationRouteObject()->generate($this->_getDestinationParameters());
      break;

      case 'content':
        $destination = $this->_redirect->getContent()->getUrl();
      break;
    }
    return $destination;
  }
}