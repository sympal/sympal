<?php

class Basesympal_content_types_Actions extends autoSympal_content_typesActions
{
  public function preExecute()
  {
    parent::preExecute();

    $this->useAdminTheme();
  }
}