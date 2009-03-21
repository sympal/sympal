<?php

class sfSympalUser extends sfGuardSecurityUser
{
  protected $_forwarded = false;

  public function checkEntitySecurity($entity)
  {
    $access = true;
    $allPermissions = $entity->getAllPermissions();

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
      $user = $this->getGuardUser();
      Doctrine::getTable('Entity')
        ->createQuery()
        ->update()
        ->set('locked_by', 'NULL')
        ->where('locked_by = ?', $user->id)
        ->execute();
    }
    return $mode;
  }

  public function getOpenEntityLock()
  {
    $q = Doctrine_Query::create()
      ->from('Entity e')
      ->leftJoin('e.Type t')
      ->andWhere('e.locked_by = ?', $this->getGuardUser()->getId());

    $lock = $q->fetchOne();
    if ($lock)
    {
      Doctrine::initializeModels(array($lock['Type']['name']));
      return $lock;
    } else {
      return false;
    }
  }
}