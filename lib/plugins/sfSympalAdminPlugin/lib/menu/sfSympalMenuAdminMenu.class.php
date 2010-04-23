<?php

/**
 * Generic menu class allows a menu item to be shown if the current user
 * has credentials matching its credentials or any of the credentials
 * of its children
 * 
 * @package     sfSympalAdminPlugin
 * @subpackage  menu
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalMenuAdminMenu extends sfSympalMenu
{
  public function getCredentials()
  {
    $credentials = $this->_credentials;
    foreach ($this->getChildren() as $child)
    {
      $credentials = array_merge($credentials, $child->getCredentials());
    }
    if ($credentials)
    {
      return array($credentials);
    }
    else
    {
      return array();
    }
  }
}