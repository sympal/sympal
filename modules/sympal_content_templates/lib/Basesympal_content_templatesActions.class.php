<?php

class Basesympal_content_templatesActions extends autoSympal_content_templatesActions
{
  public function preExecute()
  {
    parent::preExecute();

    $this->useAdminLayout();
  }
}