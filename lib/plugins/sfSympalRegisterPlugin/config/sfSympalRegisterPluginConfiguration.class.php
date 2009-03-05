<?php

class sfSympalRegisterPluginConfiguration extends sfPluginConfiguration
{
  public 
    $dependencies = array(
      'sfSympalPlugin',
      'sfDoctrineGuardPlugin'
    );

  public function initialize()
  {
    
  }
}