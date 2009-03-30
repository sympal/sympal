<?php

class Basesympal_authComponents extends sfComponents
{
  public function executeSignin_form()
  {
    $class = sfConfig::get('app_sf_guard_plugin_signin_form', 'FormSignin'); 
    $this->form = new $class();
  }
}