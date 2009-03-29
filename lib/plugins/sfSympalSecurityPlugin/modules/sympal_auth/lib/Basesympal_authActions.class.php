<?php

require_once(dirname(__FILE__).'/../../../../sfDoctrineGuardPlugin/modules/sfGuardAuth/lib/BasesfGuardAuthActions.class.php');
require_once(dirname(__FILE__).'/../../../../sfDoctrineGuardPlugin/modules/sfGuardAuth/actions/actions.class.php');

class Basesympal_authActions extends sfGuardAuthActions
{
  public function executeSignin($request)
  {
    sfSympalToolkit::loadDefaultLayout();
    parent::executeSignin($request);
  }
}