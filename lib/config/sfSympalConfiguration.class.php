<?php

/**
 * Main configuration class for sympal
 * 
 * @package     sfSympalPlugin
 * @subpackage  config
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-03-26
 * @version     svn:$Id$ $Author$
 */
class sfSympalConfiguration
{
  protected
    $_dispatcher,
    $_projectConfiguration,
    $_sympalContext,
    $_bootstrap,
    $_allManageablePlugins,
    $_contentTypePlugins;
  
  protected
    $_plugins,
    $_pluginPaths;
  
  /**
   * Information that is generated and cached
   */
  protected
    $_modules = null,
    $_layouts = null;
  
  protected
    $_cacheManager;

  public function __construct(ProjectConfiguration $projectConfiguration)
  {
    /*
     * We disable Symfony autoload again feature because it is too slow in dev mode
     * If you introduce a new class when using sympal you just must clear your
     * cache manually
     */
    sfAutoloadAgain::getInstance()->unregister();

    $this->_projectConfiguration = $projectConfiguration;
    $this->_dispatcher = $projectConfiguration->getEventDispatcher();

    $this->_configureDoctrine();

    // Listen to the sympal.load event to perform some context-dependent tasks
    $this->_dispatcher->connect('sympal.load', array($this, 'bootstrapFromContext'));
    
    $this->_dispatcher->connect('sympal.cache.prime', array($this, 'listenSympalCachePrime'));
  }

  /**
   * Configure the Doctrine manager for Sympal
   *
   * @return void
   */
  private function _configureDoctrine()
  {
    if (!class_exists('Doctrine_Manager'))
    {
      return;
    }
    
    $doctrineManager = Doctrine_Manager::getInstance();
    $doctrineManager->setAttribute(Doctrine_Core::ATTR_HYDRATE_OVERWRITE, false);
    $doctrineManager->setAttribute(Doctrine_Core::ATTR_QUERY_CLASS, 'sfSympalDoctrineQuery');
    $doctrineManager->setAttribute(Doctrine_Core::ATTR_COLLECTION_CLASS, 'sfSympalDoctrineCollection');

    if (sfSympalConfig::get('orm_cache', 'enabled', true))
    {
      $driver = sfSympalCacheManager::getOrmCacheDriver();

      $doctrineManager->setAttribute(Doctrine_Core::ATTR_QUERY_CACHE, $driver);

      if (sfSympalConfig::get('orm_cache', 'result', false))
      {
        $doctrineManager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE, $driver);
        $doctrineManager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE_LIFESPAN, sfSympalConfig::get('orm_cache', 'lifetime', 86400));
      }
    }
  }

  /**
   * Get the current ProjectConfiguration instance
   *
   * @return ProjectConfiguration $projectConfiguration
   */
  public function getProjectConfiguration()
  {
    return $this->_projectConfiguration;
  }

  /**
   * Get array of core Sympal plugins
   *
   * @return array $corePlugins
   */
  public function getCorePlugins()
  {
    return sfSympalPluginConfiguration::$corePlugins;
  }

  /**
   * Listens to the sympal.load event
   */
  public function bootstrapFromContext(sfEvent $event)
  {
    $this->_sympalContext = $event->getSubject();
    // give this object access to the cache manager
    $this->_cacheManager = $event->getSubject()->getService('cache_manager');
  }

  /**
   * Get array of plugins that are downloaded and installed to your project
   *
   * @return array $installedPlugins
   */
  public function getDownloadedPlugins()
  {
    $downloadedPlugins = array_diff($this->getPlugins(), $this->getCorePlugins());
    unset($downloadedPlugins[array_search('sfSympalPlugin', $downloadedPlugins)]);

    return $downloadedPlugins;
  }

  /**
   * Get array of available addon plugins
   *
   * @return array $addonPlugins
   */
  public function getDownloadablePlugins()
  {
    return sfSympalPluginToolkit::getDownloadablePlugins();
  }

  /**
   * Get array of all manageable plugins that can be downloaded, installed, uninstalled, etc.
   *
   * @return array $allManageablePlugins
   */
  public function getAllManageablePlugins()
  {
    if ($this->_allManageablePlugins === null)
    {
      $this->_allManageablePlugins = array_merge($this->getDownloadablePlugins(), $this->getDownloadedPlugins());
      $this->_allManageablePlugins = array_unique($this->_allManageablePlugins);
    }
    return $this->_allManageablePlugins;
  }

  /**
   * Get array of all "Sympal" plugins
   *
   * @return array $plugins
   */
  public function getPlugins()
  {
    if ($this->_plugins === null)
    {
      $this->_plugins = array_keys($this->getPluginPaths());
    }

    return $this->_plugins;
  }

  /**
   * Get paths to all Sympal plugins
   * 
   * A Sympal plugin is defined as any that contains "sfSympal" in its name
   *
   * @return array $pluginPaths
   */
  public function getPluginPaths()
  {
    if ($this->_pluginPaths === null)
    {
      $pluginPaths = $this->getProjectConfiguration()->getAllPluginPaths();
      $this->_pluginPaths = array();
      foreach ($pluginPaths as $pluginName => $path)
      {
        if (strpos($pluginName, 'sfSympal') !== false)
        {
          $this->_pluginPaths[$pluginName] = $path;
        }
      }
    }

    return $this->_pluginPaths;
  }

  /**
   * Returns whether or not a plugin exists in the project
   */
  public function pluginExists($name)
  {
    return in_array($name, $this->getPlugins());
  }

  /**
   * Get array of all modules
   *
   * @return array $modules
   */
  public function getModules()
  {
    if ($this->_modules === null)
    {
      // check if it's in the cache
      if ($this->getCache('configuration.modules'))
      {
        $this->_modules = $this->getCache('configuration.modules');
      }
      else
      {
        $this->_modules = $this->_generateModulesArray();
        $this->setCache('configuration.modules', $this->_modules);
      }
    }

    return $this->_modules;
  }

  /**
   * Get array of all layouts
   *
   * @return array $layouts
   */
  public function getLayouts()
  {
    if ($this->_layouts === null)
    {
      // check if it's in the cache
      if ($this->getCache('configuration.layouts'))
      {
        $this->_layouts = $this->getCache('configuration.layouts');
      }
      else
      {
        $this->_layouts = $this->_generateLayoutsCache();
        $this->setCache('configuration.layouts', $this->_layouts);
      }
    }

    return $this->_layouts;
  }

  /**
   * Returns a value from the cache object (if there is one)
   * 
   * @param string $name The name/key of the cache to return
   * @param mixed $default The default value to return if the cache isn't found
   */
  public function getCache($name = null, $default = null)
  {
    if ($name === null)
    {
      throw new sfException('getCache() is deprecated, use getCacheManager()');
    }

    return $this->getCacheManager() ? $this->getCacheManager()->get($name, $default) : $default;
  }

  /**
   * Set a value to the cache (if there is a cache object
   * 
   * @param string $name The name/key of the cache to set
   * @param mixed $value The value to set to the cache
   * 
   * @return boolean Whether or not the cache was set
   */
  public function setCache($name, $value)
  {
    return $this->getCacheManager() ? $this->getCacheManager()->set($name, $value) : false;
  }

  public function getCacheManager()
  {
    return $this->_cacheManager;
  }

  /**
   * Returns the event dispatcher
   * 
   * @return sfEventDispatcher
   */
  public function getEventDispatcher()
  {
    return $this->_dispatcher;
  }

  /**
   * Returns the current sympal context
   * 
   * This will not return the context until it has been created - it's not
   * automatically available.
   * 
   * @return sfSympalContext
   */
  public function getSympalContext()
  {
    return $this->_sympalContext;
  }

  /**
   * Find all modules that exist in this project and application
   *
   * @return array
   */
  protected function _generateModulesArray()
  {
    $modules = array();
    $paths = $this->getPluginPaths();
    $paths['sfDoctrineGuardPlugin'] = $this->getProjectConfiguration()->getPluginConfiguration('sfDoctrineGuardPlugin')->getRootDir();
    $paths['application'] = sfConfig::get('sf_app_dir');

    foreach ($paths as $path)
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
            $modules[] = $info['basename'];
          }
        }
      }
    }

    return $modules;
  }

  /**
   * Find all the layouts that exist for this project and application
   *
   * @return array
   */
  protected function _generateLayoutsCache()
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

    $layoutsCache = array();
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
      $layoutsCache[$path] = $name;
    }

    return $layoutsCache;
  }

  /**
   * Listens to the sympal.cache.prime event.
   */
  public function listenSympalCachePrime(sfEvent $event)
  {
    $this->_generateLayoutsCache();
    $this->_generateModulesArray();
  }

  /**
   * Get the active sfSympalConfiguration instance
   *
   * @return sfSympalConfiguration $sympalConfiguration
   */
  public static function getActive()
  {
    return sfProjectConfiguration::getActive()->getPluginConfiguration('sfSympalPlugin')->getSympalConfiguration();
  }

  /**
   * Calls methods defined via sfEventDispatcher.
   *
   * @param string $method The method name
   * @param array  $arguments The method arguments
   *
   * @return mixed The returned value of the called method
   *
   * @throws sfException If called method is undefined
   */
  public function __call($method, $arguments)
  {
    $event = $this->_dispatcher->notifyUntil(new sfEvent($this, 'sympal.configuration.method_not_found', array('method' => $method, 'arguments' => $arguments)));
    if (!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }

    return $event->getReturnValue();
  }
}