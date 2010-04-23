<?php

class sfSympalPluginApi extends sfPluginApi
{
  public function __construct($username = null, $password = null, $cacheDir = null)
  {
    if (!$username)
    {
      $username = sfSympalConfig::getDeep('plugin_manager', 'plugin_api', 'username');
    }

    if (!$password)
    {
      $password = sfSympalConfig::get('plugin_manager', 'plugin_api', 'password');
    }

    if (!$cacheDir)
    {
      $cacheDir = sfConfig::get('sf_cache_dir').'/sympal/plugins_api';
    }

    parent::__construct($username, $password, $cacheDir);
  }
}