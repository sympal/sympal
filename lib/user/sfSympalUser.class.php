<?php

class sfSympalUser extends sfBasicSecurityUser
{
  protected
    $_user             = null,
    $_forwarded       = false,
    $_flash           = false,
    $_openContentLock = null;

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

  public function isEditMode($bool = null)
  {
    if (!is_null($bool))
    {
      $this->setAttribute('sympal_edit', $bool);
    }
    return sfSympalToolkit::isEditMode();
  }

  public function toggleEditMode()
  {
    $this->isEditMode(!$this->isEditMode());
    $mode = $this->isEditMode() ? 'on':'off';

    if ($mode == 'off')
    {
      $this->releaseOpenLock();
    }

    return $mode;
  }

  public function obtainContentLock(Content $content)
  {
    if (!sfSympalToolkit::isEditMode())
    {
      return false;
    }

    $lock = $content->obtainLock($this);

    if (is_bool($lock))
    {
      $title = $content->getHeaderTitle();
      if ($lock)
      {
        $this->_openContentLock = $content;

        $this->setFlash('notice', 'Lock obtained successfully on "'.$title.'"');
      } else {
        if ($content->locked_by)
        {
          $lockedBy = $content['LockedBy']['username'];
          $this->setFlash('error', 'Lock could not be obtained on "'.$title.'" because the user "'.$lockedBy.'" is editing it');
        } else {
          $this->setFlash('error', 'Lock could not be obtained on "'.$title.'"');
        }
      }
    }
  }

  public function getOpenContentLock()
  {
    if (!$this->_openContentLock)
    {
      $q = Doctrine_Query::create()
        ->from('Content e')
        ->leftJoin('e.Type t')
        ->andWhere('e.locked_by = ?', $this->getSympalUser()->getId());

      $lock = $q->fetchOne();
      if ($lock)
      {
        Doctrine_Core::initializeModels(array($lock['Type']['name']));
        $this->_openContentLock = $lock;
      } else {
        $this->_openContentLock = false;
      }
    }

    return $this->_openContentLock;
  }

  public function releaseOpenLock()
  {
    $user = $this->getSympalUser();

    $count = Doctrine_Core::getTable('Content')
      ->createQuery()
      ->update()
      ->set('locked_by', 'NULL')
      ->where('locked_by = ?', $user->id)
      ->execute();
  }

  public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = array())
  {
    parent::initialize($dispatcher, $storage, $options);

    if (!$this->isAuthenticated())
    {
      // remove user if timeout
      $this->getAttributeHolder()->removeNamespace('sfSympalUser');
      $this->_user = null;
    }
  }

  public function getReferer($default)
  {
    $referer = $this->getAttribute('referer', $default);
    $this->getAttributeHolder()->remove('referer');

    return $referer;
  }

  public function setReferer($referer)
  {
    if (!$this->hasAttribute('referer'))
    {
      $this->setAttribute('referer', $referer);
    }
  }

  public function hasCredential($credential, $useAnd = true)
  {
    if (empty($credential))
    {
      return true;
    }

    if (!$this->getSympalUser())
    {
      return false;
    }

    if ($this->getSympalUser()->getIsSuperAdmin())
    {
      return true;
    }

    return parent::hasCredential($credential, $useAnd);
  }

  public function isSuperAdmin()
  {
    return $this->getSympalUser() ? $this->getSympalUser()->getIsSuperAdmin() : false;
  }

  public function isAnonymous()
  {
    return !$this->isAuthenticated();
  }

  public function signIn($user, $remember = false, $con = null)
  {
    sfProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($this, 'sympal.user.pre_signin', array('user' => $user, 'remember' => $remember, 'con' => $con)));

    // signin
    $this->setAttribute('user_id', $user->getId(), 'sfSympalUser');
    $this->setAuthenticated(true);
    $this->clearCredentials();
    $this->addCredentials($user->getAllPermissionNames());

    // save last login
    $user->setLastLogin(date('Y-m-d H:i:s'));
    $user->save($con);

    // remember?
    if ($remember)
    {
      $expiration_age = sfSympalConfig::get('sfSympalUserPlugin', 'remember_me_key_expiration_age', 15 * 24 * 3600);
      // remove old keys
      Doctrine_Query::create()
        ->delete()
        ->from('RememberKey k')
        ->where('created_at < ?', date('Y-m-d H:i:s', time() - $expiration_age))
        ->execute();

      // remove other keys from this user
      Doctrine_Query::create()
        ->delete()
        ->from('RememberKey k')
        ->where('k.user_id = ?', $user->getId())
        ->execute();

      // generate new keys
      $key = $this->generateRandomKey();

      // save key
      $rk = new RememberKey();
      $rk->setRememberKey($key);
      $rk->setUser($user);
      $rk->setIpAddress($_SERVER['REMOTE_ADDR']);
      $rk->save($con);

      // make key as a cookie
      $remember_cookie = sfSympalConfig::get('sfSympalUserPlugin', 'remember_me_cookie_name', 'sfRemember');
      sfContext::getInstance()->getResponse()->setCookie($remember_cookie, $key, time() + $expiration_age);
    }

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

  public function signOut()
  {
    $this->getAttributeHolder()->removeNamespace('sfSympalUser');
    $this->_user = null;
    $this->clearCredentials();
    $this->setAuthenticated(false);
    $expiration_age = sfSympalConfig::get('sfSympalUserPlugin', 'remember_me_key_expiration_age', 15 * 24 * 3600);
    $remember_cookie = sfSympalConfig::get('sfSympalUserPlugin', 'remember_me_cookie_name', 'sfRemember');
    sfContext::getInstance()->getResponse()->setCookie($remember_cookie, '', time() - $expiration_age);
  }

  public function getSympalUser()
  {
    if (!$this->_user && $id = $this->getAttribute('user_id', null, 'sfSympalUser'))
    {
      $this->_user = Doctrine_Core::getTable('User')->find($id);

      if (!$this->_user)
      {
        // the user does not exist anymore in the database
        $this->signOut();

        throw new sfException('The user does not exist anymore in the database.');
      }
    }

    return $this->_user;
  }

  public function __toString()
  {
    return $this->getSympalUser()->__toString();
  }

  public function getUsername()
  {
    return $this->getSympalUser()->getUsername();
  }

  public function getName()
  {
    return $this->getSympalUser()->getName();
  }

  public function getEmail()
  {
    return $this->getSympalUser()->getEmail();
  }

  public function setPassword($password, $con = null)
  {
    $this->getSympalUser()->setPassword($password);
    $this->getSympalUser()->save($con);
  }

  public function checkPassword($password)
  {
    return $this->getSympalUser()->checkPassword($password);
  }

  public function hasGroup($name)
  {
    return $this->getSympalUser() ? $this->getSympalUser()->hasGroup($name) : false;
  }

  public function getGroups()
  {
    return $this->getSympalUser() ? $this->getSympalUser()->getGroups() : array();
  }

  public function getGroupNames()
  {
    return $this->getSympalUser() ? $this->getSympalUser()->getGroupNames() : array();
  }

  public function hasPermission($name)
  {
    return $this->getSympalUser() ? $this->getSympalUser()->hasPermission($name) : false;
  }

  public function getPermissions()
  {
    return $this->getSympalUser()->getPermissions();
  }

  public function getPermissionNames()
  {
    return $this->getSympalUser() ? $this->getSympalUser()->getPermissionNames() : array();
  }

  public function getAllPermissions()
  {
    return $this->getSympalUser() ? $this->getSympalUser()->getAllPermissions() : array();
  }

  public function getAllPermissionNames()
  {
    return $this->getSympalUser() ? $this->getSympalUser()->getAllPermissionNames() : array();
  }

  public function addGroupByName($name, $con = null)
  {
    return $this->getSympalUser()->addGroupByName($name, $con);
  }

  public function addPermissionByName($name, $con = null)
  {
    return $this->getSympalUser()->addPermissionByName($name, $con);
  }

  public function __call($method, $arguments)
  {
    return sfSympalExtendClass::extendEvent($this, $method, $arguments);
  }
}