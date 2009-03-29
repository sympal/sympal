<?php

class sfSympalPluginToolkit
{
  public static function checkPluginDependencies($pluginName, $dependencies)
  {
    $context = sfContext::getInstance();
    $configuration = $context->getConfiguration();
    $pluginConfiguration = $configuration->getPluginConfiguration($pluginName);

    $plugins = $configuration->getPlugins();

    $dependencies = (array) $dependencies;
    foreach ($dependencies as $dependency)
    {
      if (!in_array($dependency, $plugins))
      {
        throw new sfException(
          sprintf(
            'Dependency check failed for "%s". Missing plugin named "%s".'."\n\n".
            'The following plugins are required: %s',
            $pluginConfiguration->getName(),
            $dependency,
            implode(', ', $dependencies)
          )
        );
      }
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

    try {
      sfContext::getInstance()->getConfiguration()->getPluginConfiguration($pluginName);
      return true;
    } catch (Exception $e) {
      return false;
    }
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

  public static function getAvailablePluginPaths()
  {
    $cachePath = sfConfig::get('sf_cache_dir').'/sympal_available_plugins.cache';
    if (!file_exists($cachePath))
    {
      $installedPlugins = ProjectConfiguration::getActive()->getPlugins();

      $available = array();
      $paths = sfSympalConfig::get('sympal_plugin_svn_sources');

      foreach ($paths as $path)
      {
        if (is_dir($path))
        {
          $find = sfFinder::type('dir')->maxdepth(1)->name('sfSympal*Plugin')->in($path);
          foreach ($find as $p)
          {
            $info = pathinfo($p);
            $available[$info['basename']] = $p;
          }
        } else {
          $html = file_get_contents($path);
          preg_match_all("/sfSympal(.*)Plugin/", strip_tags($html), $matches);
          foreach ($matches[0] as $plugin)
          {
            $available[$plugin] = $path;
          }
        }
      }

      $available = array_unique($available);

      $cachePath = sfConfig::get('sf_cache_dir').'/sympal_available_plugins.cache';
      file_put_contents($cachePath, serialize($available));
    } else {
      $content = file_get_contents($cachePath);
      $available = unserialize($content);
    }
    return $available;
  }

  public static function getAvailablePlugins()
  {
    return array_keys(self::getAvailablePluginPaths());
  }

  public static function getPluginDownloadPath($name)
  {
    $name = self::getShortPluginName($name);
    $pluginName = self::getLongPluginName($name);

    $e = explode('.', SYMFONY_VERSION);
    $version = $e[0].'.'.$e[1];

    $paths = self::getAvailablePluginPaths();
    $path = '';
    foreach ($paths as $pathPluginName => $path)
    {
      if ($pluginName == $pathPluginName)
      {
        $branchSvnPath = $path.'/'.$pluginName.'/branches/'.$version;
        $trunkSvnPath = $path.'/'.$pluginName.'/trunk';
        if (@file_get_contents($branchSvnPath) !== false)
        {
          $path = $branchSvnPath;
        } else if (@file_get_contents($trunkSvnPath) !== false) {
          $path = $trunkSvnPath;
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

  public static function isPluginAvailable($name)
  {
    $pluginName = self::getLongPluginName($name);
    $availablePlugins = self::getAvailablePlugins();

    return in_array($availablePlugins, $pluginName);
  }
}