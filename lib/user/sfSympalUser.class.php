<?php

class sfSympalUser extends sfGuardSecurityUser
{
  protected
    $_forwarded       = false;

  public function isEditMode()
  {
    return sfSympalToolkit::isEditMode();
  }

  public function hasAccessToContent($content)
  {
    $access = true;
    $allPermissions = $content->getAllPermissions();

    if ($this->isAuthenticated() && !$this->hasCredential($allPermissions))
    {
      $access = false;
    }

    if (!$this->isAuthenticated() && !empty($allPermissions))
    {
      $access = false;
    }
    
    return $access;
  }

  public function checkContentSecurity($content)
  {
    $access = $this->hasAccessToContent($content);
    if (!$access && !$this->_forwarded)
    {
      $this->_forwarded = true;
      sfContext::getInstance()->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));
      throw new sfStopException();
    }
    return $access;
  }

  public function signIn($user, $remember = false, $con = null)
  {
    sfProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($this, 'sympal.user.pre_signin', array('user' => $user, 'remember' => $remember, 'con' => $con)));

    parent::signIn($user, $remember, $con);

    sfProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($this, 'sympal.user.post_signin', array('user' => $user, 'remember' => $remember, 'con' => $con)));
  }

  protected function generateRandomKey($len = 20)
  {
    $string = '';
    $pool   = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    for ($i = 1; $i <= $len; $i++)
    {
      $string .= substr($pool, rand(0, 61), 1);
    }

    return md5($string);
  }

  public function getSympalUser()
  {
    return $this->getGuardUser();
  }

  public function __call($method, $arguments)
  {
    return sfSympalExtendClass::extendEvent($this, $method, $arguments);
  }
}