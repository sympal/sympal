<?php

class sfSympalUser extends sfGuardSecurityUser
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
}