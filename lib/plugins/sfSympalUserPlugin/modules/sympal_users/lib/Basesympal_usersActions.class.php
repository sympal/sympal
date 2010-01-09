<?php

require_once dirname(__FILE__).'/../lib/sympal_usersGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/sympal_usersGeneratorHelper.class.php';

class Basesympal_usersActions extends autosympal_usersActions
{
  public function preExecute()
  {
    parent::preExecute();

    $this->loadAdminTheme();
  }
}