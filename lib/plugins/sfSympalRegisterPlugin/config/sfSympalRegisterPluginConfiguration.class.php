<?php

class sfSympalRegisterPluginConfiguration extends sfPluginConfiguration
{
  public static
    $dependencies = array(
      'sfSympalPlugin',
      'sfDoctrineGuardPlugin'
    );

  public function initialize()
  {
    
  }
}