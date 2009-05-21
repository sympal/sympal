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
    $modules = sfConfig::get('sf_enabled_modules', array());
    if (sfSympalConfig::get('enable_all_modules'))
    {
      $modules = array_merge($modules, $this->getModules());
    }
    $modules = array_merge($modules, sfSympalConfig::get('enabled_modules', null, array()));

    sfConfig::set('sf_enabled_modules', $modules);

    sfConfig::set('sf_admin_module_web_dir', sfSympalConfig::get('admin_module_web_dir', '/sfSympalPlugin'));

    if (sfConfig::get('sf_login_module') == 'default')
    {
      sfConfig::set('sf_login_module', 'sympal_auth');
      sfConfig::set('sf_login_action', 'signin');
    }

    if (sfConfig::get('sf_secure_module') == 'default')
    {
      sfConfig::set('sf_secure_module', 'sympal_default');
      sfConfig::set('sf_secure_action', 'secure');
    }

    if (sfConfig::get('sf_error_404_module') == 'default')
    {
      sfConfig::set('sf_error_404_module', 'sympal_default');
      sfConfig::set('sf_error_404_action', 'error404');
    }

    if (sfConfig::get('sf_module_disabled_module') == 'default')
    {
      sfConfig::set('sf_module_disabled_module', 'sympal_default');
      sfConfig::set('sf_module_disabled_action', 'disabled');
    }

    $options = array('baseClassName' => 'sfSympalDoctrineRecord');
    $options = array_merge(sfConfig::get('doctrine_model_builder_options', array()), $options);
    sfConfig::set('doctrine_model_builder_options', $options);

    $this->_dispatcher->connect('context.load_factories', array($this, 'bootstrap'));
    $this->_dispatcher->connect('component.method_not_found', array(new sfSympalActions(), 'extend'));
  }

  public function setup()
  {
  }

  public function configure()
  {
  }


  public function _handleInstall()
  {
    $sfContext = sfContext::getInstance();
    $request = $sfContext->getRequest();
    $environment = sfConfig::get('sf_environment');
    $module = $request->getParameter('module');

    // Redirect to install module if...
    //  not in test environment
    //  sympal has not been installed
    //  module is not already sympal_install
    if ($environment != 'test' && !sfSympalConfig::get('installed') && $module != 'sympal_install')
    {
      $sfContext->getController()->redirect('@sympal_install');
    }
  }

  public function bootstrap()
  {
    sfSympalContext::createInstance(sfConfig::get('app_sympal_config_site_slug', sfConfig::get('sf_app')), sfContext::getInstance());

    $this->_primeCache();
    $this->_handleInstall();

    $this->_projectConfiguration->loadHelpers(array('Sympal', 'I18N'));

    $request = sfContext::getInstance()->getRequest();

    if (!$request->isXmlHttpRequest() && $request->getParameter('module') != 'sympal_content_renderer')
    {
      sfSympalToolkit::changeLayout(sfSympalConfig::get('default_layout'));
    }

    if (sfConfig::get('sf_debug'))
    {
      $this->checkDependencies();
    }

    $contentTypes = sfSympalToolkit::getContentTypesCache();
    Doctrine::initializeModels($contentTypes);
  }

  protected function _isCachePrimed()
  {
    return file_exists(sfConfig::get('sf_cache_dir').'/sympal/cache_primed.cache');
  }

  public function _primeCache()
  {
    if ($this->_isCachePrimed())
    {
      return true;
    }

    if (!is_dir($path = sfConfig::get('sf_cache_dir').'/sympal'))
    {
      mkdir($path, 0777, true);
    }

    $this->_writeContentTypesCache();
    $this->_writeHelperAutoloadCache();

    touch(sfConfig::get('sf_cache_dir').'/sympal/cache_primed.cache');
  }

  protected function _writeHelperAutoloadCache()
  {
    $cachePath = sfConfig::get('sf_cache_dir').'/sympal/helper_autoload.cache';
    if (!file_exists($cachePath))
    {
      $cache = array();
      $dirs = $this->_projectConfiguration->getHelperDirs();
      foreach ($dirs as $dir)
      {
        $helpers = sfFinder::type('file')->name('*Helper.php')->in($dir);
        foreach ($helpers as $helper)
        {
          $lines = file($helper);
          foreach ($lines as $line)
          {
            preg_match("/function (.*)\(/", $line, $matches);
            if ($matches)
            {
              $function = $matches[1];
              $e = explode('(', $function);
              $function = $e[0];
              $cache[$function] = $helper;
            }
          }
        }
      }
      file_put_contents($cachePath, serialize($cache));
    }
  }

  protected function _writeContentTypesCache()
  {
    $cachePath = sfConfig::get('sf_cache_dir').'/sympal/content_types.cache';
    if (!file_exists($cachePath))
    {
      try {
        $cachePath = sfConfig::get('sf_cache_dir').'/sympal/content_types.cache';
        if (!file_exists($cachePath))
        {
          $typesArray = array();
          $contentTypes = Doctrine::getTable('ContentType')->findAll();
          foreach ($contentTypes as $contentType)
          {
            $typesArray[$contentType['id']] = $contentType['name'];
          }
          file_put_contents($cachePath, serialize($typesArray));
        }
      } catch (Exception $e) {}
    }
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

  public function checkDependencies()
  {
    foreach ($this->_projectConfiguration->getPlugins() as $pluginName)
    {
      if (strpos($pluginName, 'sfSympal') !== false)
      {
        $dependencies = sfSympalPluginToolkit::getPluginDependencies($pluginName);
        sfSympalPluginToolkit::checkPluginDependencies($pluginName, $dependencies);
      }
    }
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

          foreach ($find as $module)
          {
            if (is_dir($module))
            {
              $info = pathinfo($module);
              $this->_modules[] = $info['basename'];
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
      $cachePath = sfConfig::get('sf_cache_dir').'/sympal/layouts.cache';
      if (!file_exists($cachePath))
      {
        $layouts = array();
        foreach ($this->getPluginPaths() as $plugin => $path)
        {
          $path = $path.'/templates';
          $find = glob($path.'/*.php');
          $layouts = array_merge($layouts, $find);
        }

        $find = glob(sfConfig::get('sf_app_dir').'/templates/*.php');
        $layouts = array_merge($layouts, $find);

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