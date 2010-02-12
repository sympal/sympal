<?php

class sfSympalSearchPluginConfiguration extends sfPluginConfiguration
{
  private $_search;

  public function initialize()
  {
    set_include_path(dirname(__FILE__).'/../lib'.PATH_SEPARATOR.get_include_path());
    require_once dirname(__FILE__).'/../lib/Zend/Loader/Autoloader.php';
    Zend_Loader_Autoloader::getInstance();
  }
}