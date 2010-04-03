<?php

/**
 * Child sfUser class for handling session related functionality for Sympal
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalUser extends sfGuardSecurityUser
{
  protected
    $_forwarded  = false,
    $_isEditMode = null;

  /**
   * Get the current culture we are editing content for
   *
   * @return string $culture
   */
  public function getEditCulture()
  {
    if ($editCulture = $this->getAttribute('sympal_edit_culture'))
    {
      return $editCulture;
    } else {
      return $this->getCulture();
    }
  }

  /**
   * Set the current culture we are editing content for
   *
   * @param string $culture 
   * @return void
   */
  public function setEditCulture($culture)
  {
    $this->setAttribute('sympal_edit_culture', $culture);
  }

  /**
   * Perform the check to determine if we are in edit mode or not
   *
   * @return void
   */
  public function doIsEditModeCheck()
  {
    $content = sfSympalContext::getInstance()->getService('site_manager')->getCurrentContent();
    if (($content && $content->getPubliclyEditable())
      || ($content && $content->getAllEditPermissions() && $this->hasCredential($content->getAllEditPermissions()))
      || ($this->isAuthenticated() && $this->hasCredential('ManageContent'))
    )
    {
      $this->_isEditMode = true;
    } else {
      $this->_isEditMode = false;
    }
    $this->_isEditMode = sfApplicationConfiguration::getActive()->getEventDispatcher()->filter(new sfEvent($this, 'sympal.filter_is_edit_mode'), $this->_isEditMode)->getReturnValue();
  }

  /**
   * Check if we are in edit mode or not
   *
   * @param boolean $forceCheckAgain
   * @return boolean
   */
  public function isEditMode($forceCheckAgain = false)
  {
    if ($this->_isEditMode === null || $forceCheckAgain === true)
    {
      $this->doIsEditModeCheck();
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

  /**
   * Check if this user has access to view the given content
   *
   * @param sfSympalContent $content 
   * @return boolean
   */
  public function hasAccessToViewContent($content)
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

  /**
   * Check if a user has access to content and forward to the secure module if he does not
   *
   * @param sfSympalContent $content
   * @return boolean
   * @throws sfStopException
   */
  public function checkContentSecurity($content)
  {
    $access = $this->hasAccessToViewContent($content);
    if (!$access && !$this->_forwarded)
    {
      $this->_forwarded = true;
      sfContext::getInstance()->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));
      throw new sfStopException();
    }
    return $access;
  }

  /**
   * Get the sfGuardUser instance for the logged in user
   *
   * @return sfGuardUser $guardUser
   */
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