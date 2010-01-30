<?php

class Basesympal_content_slotsActions extends autoSympal_content_slotsActions
{
  public function preExecute()
  {
    parent::preExecute();

    $this->getContext()->getEventDispatcher()->connect('admin.save_object', array($this, 'listenToAdminSaveObject'));
  }

  public function listenToAdminSaveObject(sfEvent $event)
  {
    $this->clearCache();
  }
}