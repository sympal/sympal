<?php

class sfSympalPluginToolkit
{
  public static function getPluginPath($pluginName)
  {
    try {
      return ProjectConfiguration::getActive()->getPluginConfiguration($pluginName)->getRootDir();
    } catch (Exception $e) {
      return false;
    }
  }

  public static function getRequiredPlugins()
  {
    $requiredPlugins = array();

    $context = sfContext::getInstance();
    $configuration = $context->getConfiguration();

    $plugins = $configuration->getPlugins();
    foreach ($plugins as $plugin)
    {
      $dependencies = sfSympalPluginToolkit::getPluginDependencies($plugin);
      $requiredPlugins = array_merge($requiredPlugins, $dependencies);
    }

    $requiredPlugins = array_unique($requiredPlugins);

    return $requiredPlugins;
  }

  public static function getPluginDependencies($pluginName)
  {
    try {
      $refClass = new ReflectionClass($pluginName.'Configuration');
      return $refClass->getStaticPropertyValue('dependencies');
    } catch (Exception $e) {
      return array();
    }
  }

  public static function isPluginInstalled($plugin)
  {
    $pluginName = sfSympalPluginToolkit::getLongPluginName($plugin);

    return (self::isPluginDownloaded($plugin) && sfSympalConfig::get($pluginName, 'installed', false));
  }

  public static function isPluginDownloaded($plugin)
  {
    $pluginName = sfSympalPluginToolkit::getLongPluginName($plugin);

    return is_dir(self::getPluginPath($pluginName)) ? true:false;
  }

  public static function isPluginDownloadable($name) 
 	{ 
 	  $pluginName = self::getLongPluginName($name); 
 	  $availablePlugins = self::getDownloadablePlugins(); 
 	
 	  return in_array($availablePlugins, $pluginName); 
 	}

  public static function getLongPluginName($name)
  {
    if (strstr($name, 'sfSympal'))
    {
      return $name;
    } else {
      return 'sfSympal'.Doctrine_Inflector::classify(Doctrine_Inflector::tableize($name)).'Plugin';
    }
  }

  public static function getShortPluginName($name)
  {
    // Special shortening for non sympal plugins
    if (substr($name, 0, 2) == 'sf' && !strstr($name, 'sfSympal'))
    {
      return $name;
    }

    if (strstr($name, 'sfSympal'))
    {
      return substr($name, 8, strlen($name) - 14);
    } else {
      return Doctrine_Inflector::classify(Doctrine_Inflector::tableize($name));
    }
  }

  public static function getDownloadablePluginPaths()
  {
    $cachePath = sfConfig::get('sf_cache_dir').'/sympal/plugins.cache';
    if (!file_exists($cachePath))
    {
      $installedPlugins = ProjectConfiguration::getActive()->getPlugins();

      $available = array();
      $paths = sfSympalConfig::get('plugin_sources');

      foreach ($paths as $path)
      {
        if (is_dir($path))
        {
          $find = sfFinder::type('dir')->maxdepth(1)->name('sfSympal*Plugin')->in($path);
          foreach ($find as $p)
          {
            $info = pathinfo($p);
            if (!isset($available[$info['basename']]))
            {
              $available[$info['basename']] = $p;
            }
          }
        } else {
          $html = sfSympalToolkit::fileGetContents($path);
          preg_match_all("/sfSympal(.*)Plugin/", strip_tags($html), $matches);
          foreach ($matches[0] as $plugin)
          {
            if (!isset($available[$plugin]))
            {
              $available[$plugin] = $path;
            }
          }
        }
      }

      if (isset($available['sfSympalPlugin']))
      {
        unset($available['sfSympalPlugin']);
      }

      if (!is_dir($dir = dirname($cachePath)))
      {
        mkdir($dir, 0777, true);
      }

      file_put_contents($cachePath, serialize($available));
    } else {
      $content = file_get_contents($cachePath);
      $available = unserialize($content);
    }
    return $available;
  }

  public static function getDownloadablePlugins()
  {
    return array_keys(self::getDownloadablePluginPaths());
  }

  public static function getPluginDownloadPath($name)
  {
    $name = self::getShortPluginName($name);
    $pluginName = self::getLongPluginName($name);

    $e = explode('.', SYMFONY_VERSION);
    $version = $e[0].'.'.$e[1];

    $paths = self::getDownloadablePluginPaths();
    $path = '';
    foreach ($paths as $pathPluginName => $path)
    {
      if ($pluginName == $pathPluginName)
      {
        $branchSvnPath = $path.'/'.$pluginName.'/branches/'.$version;
        $trunkSvnPath = $path.'/'.$pluginName.'/trunk';
        if (sfSympalToolkit::fileGetContents($branchSvnPath) !== false || is_dir($branchSvnPath))
        {
          $path = $branchSvnPath;
          break;
        } else if (sfSympalToolkit::fileGetContents($trunkSvnPath) !== false || is_dir($trunkSvnPath)) {
          $path = $trunkSvnPath;
          break;
        } else if (is_dir($path)) {
          break;
        }
      }
    }

    if ($path)
    {
      return $path;
    } else {
      throw new sfException('Could not find download path for '.$pluginName);
    }
  }
}