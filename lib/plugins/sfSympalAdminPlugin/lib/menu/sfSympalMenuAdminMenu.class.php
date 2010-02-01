<?php

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
    } else {
      return array();
    }
  }
}