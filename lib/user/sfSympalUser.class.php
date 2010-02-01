<?php

class sfSympalUser extends sfGuardSecurityUser
{
  protected
    $_forwarded  = false,
    $_isEditMode = null;

  public function isEditMode()
  {
    if (is_null($this->_isEditMode))
    {
      $this->_isEditMode = $this->isAuthenticated() && $this->hasCredential('ManageContent');
      $this->_isEditMode = sfApplicationConfiguration::getActive()->getEventDispatcher()->filter(new sfEvent($this, 'sympal.filter_is_edit_mode'), $this->_isEditMode)->getReturnValue();
    }
    return $this->_isEditMode;
  }

  public function signIn($user, $remember = false, $con = null)
  {
    $this->_isEditMode = null;
    return parent::signIn($user, $remember, $con);
  }

  public function signOut()
  {
    $this->_isEditMode = null;
    return parent::signOut();
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

  public function setCurrentTheme($theme)
  {
    $this->setAttribute('sympal_current_theme', $theme);
  }

  public function getCurrentTheme()
  {
    return $this->getAttribute('sympal_current_theme');
  }

  public function getGuardUser()
  {
    if (!$this->user && $id = $this->getAttribute('user_id', null, 'sfGuardSecurityUser'))
    {
      $q = Doctrine_Core::getTable('sfGuardUser')->createQuery('u')
        ->where('u.id = ?', $id)
        ->limit(1);

      $q->enableSympalResultCache('sympal_get_user');

      if (!$this->user = $q->fetchOne())
      {
        // the user does not exist anymore in the database
        $this->signOut();

        // make sure that the session data is written
        $this->shutdown();

        throw new sfException('The user does not exist anymore in the database.');
      }
    }

    return $this->user;
  }
}