<?php

class Basesympal_content_slot_typesActions extends autoSympal_content_slot_typesActions
{
  public function preExecute()
  {
    parent::preExecute();

    $this->useAdminLayout();
  }
}