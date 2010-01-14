<?php

class Basesympal_content_types_Actions extends autoSympal_content_typesActions
{
  public function preExecute()
  {
    parent::preExecute();

    $this->getContext()->getEventDispatcher()->connect('admin.save_object', array($this, 'listenToAdminSaveObject'));
  }

  public function listenToAdminSaveObject(sfEvent $event)
  {
    $this->resetSympalRoutesCache();
  }
}