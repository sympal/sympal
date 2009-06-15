<?php

class sfSympalConfiguration
{
  protected
    $_dispatcher,
    $_projectConfiguration,
    $_plugins = array(),
    $_modules = array(),
    $_layouts = array();

  public function __construct(sfEventDispatcher $dispatcher, ProjectConfiguration $projectConfiguration)
  {
    $this->_dispatcher = $dispatcher;
    $this->_projectConfiguration = $projectConfiguration;
  }

  public static function getSympalConfiguration(sfEventDispatcher $dispatcher, ProjectConfiguration $projectConfiguration)
  {
    $appDir = sfConfig::get('sf_app_dir');
    $info = pathinfo($appDir);
    $appName = $info['filename'];

    if (is_dir($appDir) && file_exists($path = $appDir.'/config/'.$appName.'SympalConfiguration.class.php'))
    {  
      require_once(sfConfig::get('sf_config_dir').'/SympalProjectConfiguration.class.php');
      require_once($path);
      $className = $appName.'SympalConfiguration';
    } else if (file_exists($path = sfConfig::get('sf_config_dir').'/SympalProjectConfiguration.class.php')) {
      require_once($path);
      $className = 'SympalProjectConfiguration';
    } else {
      $className = 'sfSympalConfiguration';
    }

    $sympalConfiguration = new $className($dispatcher, $projectConfiguration);
    $sympalConfiguration->initialize();
    $sympalConfiguration->setup();
    $sympalConfiguration->configure();

    return $sympalConfiguration;
  }

  public function initialize()
  {
    $bootstrap = new sfSympalBootstrap($this);
  }

  public function getProjectConfiguration()
  {
    return $this->_projectConfiguration;
  }

  public function setup()
  {
  }

  public function configure()
  {
  }

  public function getRequiredPlugins()
  {
    $requiredPlugins = array();
    foreach ($this->_projectConfiguration->getPlugins() as $pluginName)
    {
      if (strpos($pluginName, 'sfSympal') !== false)
      {
        $dependencies = sfSympalPluginToolkit::getPluginDependencies($pluginName);
        $requiredPlugins = array_merge($requiredPlugins, $dependencies);
      }
    }

    return array_unique($requiredPlugins);
  }

  public function getCorePlugins()
  {
    return sfSympalPluginConfiguration::$dependencies;
  }

  public function getInstalledPlugins()
  {
    return $this->getOtherPlugins();
  }

  public function getAddonPlugins()
  {
    return sfSympalPluginToolkit::getAvailablePlugins();
  }

  public function getOtherPlugins()
  {
    return array_diff($this->getPlugins(), $this->getRequiredPlugins());
  }

  public function getAllManageablePlugins()
  {
    $plugins = array_merge($this->getAddonPlugins(), $this->getInstalledPlugins());
    $plugins = array_unique($plugins);

    return $plugins;
  }

  public function getPlugins()
  {
    return array_keys($this->getPluginPaths());
  }

  public function getPluginPaths()
  {
    if (!$this->_plugins)
    {
      $configuration = ProjectConfiguration::getActive();
      $pluginPaths = $configuration->getAllPluginPaths();
      $this->_plugins = array();
      foreach ($pluginPaths as $pluginName => $path)
      {
        if (strpos($pluginName, 'sfSympal') !== false)
        {
          $this->_plugins[$pluginName] = $path;
        }
      }
    }

    return $this->_plugins;
  }

  public function getModules()
  {
    if (!$this->_modules)
    {
      $cachePath = sfConfig::get('sf_cache_dir').'/sympal/modules.cache';
      if (!file_exists($cachePath))
      {
        $this->_modules = array();
        $plugins = $this->getPluginPaths();

        foreach ($plugins as $plugin => $path)
        {
          $path = $path . '/modules';
          $find = glob($path . '/*');

          if (is_array($find))
          {
            foreach ($find as $module)
            {
              if (is_dir($module))
              {
                $info = pathinfo($module);
                $this->_modules[] = $info['basename'];
              }
            }
          }
        }
        if (!is_dir($dir = dirname($cachePath)))
        {
          mkdir($dir, 0777, true);
        }
        file_put_contents($cachePath, serialize($this->_modules));
      } else {
        $serialized = file_get_contents($cachePath);
        $this->_modules = unserialize($serialized);
      }
    }

    return $this->_modules;
  }

  public function getLayouts()
  {
    if (!$this->_layouts)
    {
      $cachePath = sfConfig::get('sf_cache_dir').'/sympal/'.sfConfig::get('sf_app').'_layouts.cache';
      if (!file_exists($cachePath))
      {
        $layouts = array();
        foreach ($this->getPluginPaths() as $plugin => $path)
        {
          $path = $path.'/templates';
          $find = glob($path.'/*.php');
          if (is_array($find))
          {
            $layouts = array_merge($layouts, $find);
          }
        }

        $find = glob(sfConfig::get('sf_app_dir').'/templates/*.php');
        if (is_array($find))
        {
          $layouts = array_merge($layouts, $find);
        }

        $this->_layouts = array();
        foreach ($layouts as $path)
        {
          $info = pathinfo($path);
          $name = $info['filename'];
          // skip partial/component templates
          if ($name[0] == '_')
          {
            continue;
          }
          $path = str_replace(sfConfig::get('sf_root_dir').'/', '', $path);
          $this->_layouts[$path] = $name;
        }
        file_put_contents($cachePath, serialize($this->_layouts));
      } else {
        $serialized = file_get_contents($cachePath);
        $this->_layouts = unserialize($serialized);
      }
    }
    return $this->_layouts;
  }
}