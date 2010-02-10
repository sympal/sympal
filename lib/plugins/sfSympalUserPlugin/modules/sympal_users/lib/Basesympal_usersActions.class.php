<?php

require_once dirname(__FILE__).'/../lib/sympal_usersGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/sympal_usersGeneratorHelper.class.php';

class Basesympal_usersActions extends autosympal_usersActions
{
  public function preExecute()
  {
    parent::preExecute();
    
    $this->getContext()->getEventDispatcher()->connect('admin.delete_object', array($this, 'listenToAdminDeleteObject'));
  }

  public function listenToAdminDeleteObject(sfEvent $event)
  {
    if ($this->getUser()->getGuardUser()->getId() == $event['object']->getId())
    {
      $this->getUser()->setFlash('error', 'You cannot delete yourself from the database!');
      $this->redirect('@sympal_users');
    }
  }
}