<?php

require_once dirname(__FILE__).'/../lib/sympal_permissionsGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/sympal_permissionsGeneratorHelper.class.php';

class Basesympal_permissionsActions extends autosympal_permissionsActions
{
  public function preExecute()
  {
    parent::preExecute();

    $this->loadAdminTheme();
  }
}