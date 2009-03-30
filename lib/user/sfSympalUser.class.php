<?php

class sfSympalUser extends sfBasicSecurityUser
{
  protected
    $_forwarded       = false,
    $_flash           = false,
    $_openContentLock = null;

  public function checkContentSecurity($content)
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

    if (!$access && !$this->_forwarded)
    {
      $this->_forwarded = true;
      return sfContext::getInstance()->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));
    }
    return $access;
  }

  public function toggleEditMode()
  {
    $this->setAttribute('sympal_edit', !$this->getAttribute('sympal_edit', false));
    $mode = $this->getAttribute('sympal_edit', false) ? 'on':'off';

    if ($mode == 'off')
    {
      $this->releaseOpenLock();
    }

    return $mode;
  }

  public function addFlash($name, $value, $persist = true)
  {
    $flash = (array) parent::getFlash($name, $value, $persist);
    $flash = $flash ? $flash:array();
    $flash[] = $value;

    parent::setFlash($name, $flash);
  }

  public function setFlash($name, $value, $persist = true)
  {
    $this->addFlash($name, $value, $persist);
  }

  public function getFlash($name, $default = null)
  {
    return end($this->getFlashArray($name, $default));
  }

  public function getFlashArray($type, $default = null)
  {
    $flash = parent::getFlash($type, $default);
    $flash = array_unique($flash);

    $this->getAttributeHolder()->remove($type, null, 'symfony/user/sfUser/flash');

    return $flash;
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
        ->andWhere('e.locked_by = ?', $this->getGuardUser()->getId());

      $lock = $q->fetchOne();
      if ($lock)
      {
        Doctrine::initializeModels(array($lock['Type']['name']));
        $this->_openContentLock = $lock;
      } else {
        $this->_openContentLock = false;
      }
    }

    return $this->_openContentLock;
  }

  public function releaseOpenLock()
  {
    $user = $this->getGuardUser();

    $count = Doctrine::getTable('Content')
      ->createQuery()
      ->update()
      ->set('locked_by', 'NULL')
      ->where('locked_by = ?', $user->id)
      ->execute();

    if ($count)
    {
      $this->setFlash('notice', 'Lock released on previous content');
    }
  }

  private $user = null;

  public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = array())
  {
    parent::initialize($dispatcher, $storage, $options);

    if (!$this->isAuthenticated())
    {
      // remove user if timeout
      $this->getAttributeHolder()->removeNamespace('sfSympalUser');
      $this->user = null;
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

    if (!$this->getGuardUser())
    {
      return false;
    }

    if ($this->getGuardUser()->getIsSuperAdmin())
    {
      return true;
    }

    return parent::hasCredential($credential, $useAnd);
  }

  public function isSuperAdmin()
  {
    return $this->getGuardUser() ? $this->getGuardUser()->getIsSuperAdmin() : false;
  }

  public function isAnonymous()
  {
    return !$this->isAuthenticated();
  }

  public function signIn($user, $remember = false, $con = null)
  {
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
    $this->user = null;
    $this->clearCredentials();
    $this->setAuthenticated(false);
    $expiration_age = sfSympalConfig::get('sfSympalUserPlugin', 'remember_me_key_expiration_age', 15 * 24 * 3600);
    $remember_cookie = sfSympalConfig::get('sfSympalUserPlugin', 'remember_me_cookie_name', 'sfRemember');
    sfContext::getInstance()->getResponse()->setCookie($remember_cookie, '', time() - $expiration_age);
  }

  public function getGuardUser()
  {
    if (!$this->user && $id = $this->getAttribute('user_id', null, 'sfSympalUser'))
    {
      $this->user = Doctrine::getTable('User')->find($id);

      if (!$this->user)
      {
        // the user does not exist anymore in the database
        $this->signOut();

        throw new sfException('The user does not exist anymore in the database.');
      }
    }

    return $this->user;
  }

  public function __toString()
  {
    return $this->getGuardUser()->__toString();
  }

  public function getUsername()
  {
    return $this->getGuardUser()->getUsername();
  }

  public function getEmail()
  {
    return $this->getGuardUser()->getEmail();
  }

  public function setPassword($password, $con = null)
  {
    $this->getGuardUser()->setPassword($password);
    $this->getGuardUser()->save($con);
  }

  public function checkPassword($password)
  {
    return $this->getGuardUser()->checkPassword($password);
  }

  public function hasGroup($name)
  {
    return $this->getGuardUser() ? $this->getGuardUser()->hasGroup($name) : false;
  }

  public function getGroups()
  {
    return $this->getGuardUser() ? $this->getGuardUser()->getGroups() : array();
  }

  public function getGroupNames()
  {
    return $this->getGuardUser() ? $this->getGuardUser()->getGroupNames() : array();
  }

  public function hasPermission($name)
  {
    return $this->getGuardUser() ? $this->getGuardUser()->hasPermission($name) : false;
  }

  public function getPermissions()
  {
    return $this->getGuardUser()->getPermissions();
  }

  public function getPermissionNames()
  {
    return $this->getGuardUser() ? $this->getGuardUser()->getPermissionNames() : array();
  }

  public function getAllPermissions()
  {
    return $this->getGuardUser() ? $this->getGuardUser()->getAllPermissions() : array();
  }

  public function getAllPermissionNames()
  {
    return $this->getGuardUser() ? $this->getGuardUser()->getAllPermissionNames() : array();
  }

  public function getProfile()
  {
    return $this->getGuardUser() ? $this->getGuardUser()->getProfile() : null;
  }

  public function addGroupByName($name, $con = null)
  {
    return $this->getGuardUser()->addGroupByName($name, $con);
  }

  public function addPermissionByName($name, $con = null)
  {
    return $this->getGuardUser()->addPermissionByName($name, $con);
  }
}