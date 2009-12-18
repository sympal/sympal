<?php

/**
 * Base actions for the sfSympalUserPlugin sympal_auth module.
 * 
 * @package     sfSympalUserPlugin
 * @subpackage  sympal_auth
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class Basesympal_authActions extends sfActions
{
  public function executeSignin()
  {
    $class = sfConfig::get('app_sf_guard_plugin_signin_form', 'sfGuardFormSignin'); 
    $this->form = new $class();
  }

  public function executeSecure()
  {
  }
}