<?php

require_once dirname(__FILE__).'/../lib/Basesympal_authComponents.class.php';

class sympal_authComponents extends Basesympal_authComponents
{
  public function executeSignin_form()
  {
    $class = sfSympalConfig::get('sfSympalUserPlugin', 'signin_form', 'sfSympalUserSigninForm'); 
    $this->form = new $class();
  }
}