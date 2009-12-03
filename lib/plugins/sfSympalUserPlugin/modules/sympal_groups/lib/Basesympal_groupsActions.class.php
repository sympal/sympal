<?php

require_once dirname(__FILE__).'/../lib/sympal_groupsGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/sympal_groupsGeneratorHelper.class.php';

class Basesympal_groupsActions extends autoSympal_groupsActions
{
  public function preExecute()
  {
    parent::preExecute();

    $this->useAdminTheme();
  }
}