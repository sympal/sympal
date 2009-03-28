<?php

require_once dirname(__FILE__).'/../lib/Basesympal_authComponents.class.php';

class sympal_authComponents extends Basesympal_authComponents
{
  public function executeSignin_form()
  {
    $class = sfConfig::get('app_sf_guard_plugin_signin_form', 'sfGuardFormSignin'); 
    $this->form = new $class();
  }
}