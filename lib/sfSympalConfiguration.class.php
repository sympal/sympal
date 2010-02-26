<?php

class sfSympalConfiguration
{
  protected
    $_dispatcher,
    $_projectConfiguration,
    $_sympalContext,
    $_symfonyContext,
    $_doctrineManager,
    $_bootstrap,
    $_plugins,
    $_pluginPaths,
    $_allManageablePlugins,
    $_contentTypePlugins,
    $_requiredPlugins,
    $_modules,
    $_themes,
    $_availableThemes,
    $_cache;

  public function __construct(ProjectConfiguration $projectConfiguration)
  {
    // We disable Symfony autoload again feature because it is too slow in dev mode
    // If you introduce a new class when using sympal you just must clear your
    // cache manually
    sfAutoloadAgain::getInstance()->unregister();

    $this->_projectConfiguration = $projectConfiguration;
    $this->_dispatcher = $projectConfiguration->getEventDispatcher();
    $this->_doctrineManager = Doctrine_Manager::getInstance();

    $this->_initializeSymfonyConfig();
    $this->_markClassesAsSafe();
    $this->_configureSuperCache();
    $this->_configureDoctrine();

    new sfSympalContextLoadFactoriesListener($this->_dispatcher, $this);
    new sfSympalTaskClearCacheListener($this->_dispatcher, $this);
  }

  /**
   * Initialize some sfConfig values for Sympal
   *
   * @return void
   */
  private function _initializeSymfonyConfig()
  {
    sfConfig::set('sf_cache', sfSympalConfig::get('page_cache', 'enabled', false));
    sfConfig::set('sf_default_culture', sfSympalConfig::get('default_culture', null, 'en'));
    sfConfig::set('sf_admin_module_web_dir', sfSympalConfig::get('admin_module_web_dir', null, '/sfSympalAdminPlugin'));

    sfConfig::set('app_sf_guard_plugin_success_signin_url', sfSympalConfig::get('success_signin_url'));

    if (sfConfig::get('sf_login_module') == 'default')
    {
      sfConfig::set('sf_login_module', 'sympal_auth');
      sfConfig::set('sf_login_action', 'signin');
    }

    if (sfConfig::get('sf_secure_module') == 'default')
    {
      sfConfig::set('sf_secure_module', 'sympal_auth');
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

    sfConfig::set('sf_jquery_path', sfSympalConfig::get('jquery_reloaded', 'path'));
    sfConfig::set('sf_jquery_plugin_paths', sfSympalConfig::get('jquery_reloaded', 'plugin_paths'));
  }

  /**
   * Mark necessary Sympal classes as safe
   *
   * @return void
   */
  private function _markClassesAsSafe()
  {
    sfOutputEscaper::markClassesAsSafe(array(
      'sfSympalContent',
      'sfSympalContentTranslation',
      'sfSympalContentSlot',
      'sfSympalContentSlotTranslation',
      'sfSympalMenuItem',
      'sfSympalMenuItemTranslation',
      'sfSympalContentRenderer',
      'sfSympalMenu',
      'sfParameterHolder',
      'sfSympalDataGrid',
      'sfSympalUpgradeFromWeb',
      'sfSympalServerCheckHtmlRenderer',
      'sfSympalSitemapGenerator'
    ));
  }

  /**
   * Configure super cache if enabled
   *
   * @return void
   */
  private function _configureSuperCache()
  {
    if (sfSympalConfig::get('page_cache', 'super') && sfConfig::get('sf_cache'))
    {
      $superCache = new sfSympalSuperCache($this);
      $this->_dispatcher->connect('response.filter_content', array($superCache, 'listenToResponseFilterContent'));
    }
  }

  /**
   * Configure the Doctrine manager for Sympal
   *
   * @return void
   */
  private function _configureDoctrine()
  {
    $this->_doctrineManager->setAttribute(Doctrine_Core::ATTR_HYDRATE_OVERWRITE, false);
    $this->_doctrineManager->setAttribute(Doctrine_Core::ATTR_TABLE_CLASS, 'sfSympalDoctrineTable');
    $this->_doctrineManager->setAttribute(Doctrine_Core::ATTR_QUERY_CLASS, 'sfSympalDoctrineQuery');
    $this->_doctrineManager->setAttribute(Doctrine_Core::ATTR_COLLECTION_CLASS, 'sfSympalDoctrineCollection');

    if (sfSympalConfig::get('orm_cache', 'enabled', true))
    {
      $driver = sfSympalCache::getOrmCacheDriver();

      $this->_doctrineManager->setAttribute(Doctrine_Core::ATTR_QUERY_CACHE, $driver);

      if (sfSympalConfig::get('orm_cache', 'result', false))
      {
        $this->_doctrineManager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE, $driver);
        $this->_doctrineManager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE_LIFESPAN, sfSympalConfig::get('orm_cache', 'lifetime', 86400));
      }
    }
  }

  /**
   * Set the sfSympalCache instance for this sympal configuration instance
   *
   * @param sfSympalCache $cache 
   * @return void
   */
  public function setCache(sfSympalCache $cache)
  {
    $this->_cache = $cache;
  }

  /**
   * Set the symfony context for this sympal configuration instance
   *
   * @param sfContext $symfonyContext 
   * @return void
   */
  public function setSymfonyContext(sfContext $symfonyContext)
  {
    $this->_symfonyContext = $symfonyContext;
  }

  /**
   * Set the sympal context for this sympal configuration instance
   *
   * @param sfSympalContext $sympalContext 
   * @return void
   */
  public function setSympalContext(sfSympalContext $sympalContext)
  {
    $this->_sympalContext = $sympalContext;
  }

  /**
   * Get the current sfContext instance
   *
   * @return sfContext $symfonyContext
   */
  public function getSymfonyContext()
  {
    return $this->_symfonyContext;
  }

  /**
   * Get the Doctrine_Manager instance
   *
   * @return Doctrine_Manager $manager
   */
  public function getDoctrineManager()
  {
    return $this->_doctrineManager;
  }

  /**
   * Get the sfSympalCache instance
   *
   * @return sfSympalCache $cache
   */
  public function getCache()
  {
    return $this->_cache;
  }

  /**
   * Get the current sfSympalContext instance
   *
   * @return sfSympalContext $sympalContext
   */
  public function getSympalContext()
  {
    return $this->_sympalContext;
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
   * Set the project configuration instance to use
   *
   * @param ProjectConfiguration $projectConfiguration 
   * @return void
   */
  public function setProjectConfiguration(ProjectConfiguration $projectConfiguration)
  {
    $this->_projectConfiguration = $projectConfiguration;
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
   * Get array of plugins which contain a content type
   *
   * @return array $contentTypePlugins
   */
  public function getContentTypePlugins()
  {
    if ($this->_contentTypePlugins === null)
    {
      $this->_contentTypePlugins = array();
      $plugins = $this->getPluginPaths();

      foreach ($plugins as $plugin => $path)
      {
        $manager = new sfSympalPluginManager($plugin, $this->_projectConfiguration, new sfFormatter());
        if ($contentType = $manager->getContentTypeForPlugin())
        {
          $this->_contentTypePlugins[] = $plugin;
        }
      }
    }
    return $this->_contentTypePlugins;
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
   * Get array of all installed plugins
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
   * @return array $pluginPaths
   */
  public function getPluginPaths()
  {
    if ($this->_pluginPaths === null)
    {
      $configuration = ProjectConfiguration::getActive();
      $pluginPaths = $configuration->getAllPluginPaths();
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
   * Get array of all modules
   *
   * @return array $modules
   */
  public function getModules()
  {
    return $this->getCache()->getModules();
  }

  /**
   * Get array of all layouts
   *
   * @return array $layouts
   */
  public function getLayouts()
  {
    return $this->getCache()->getLayouts();
  }

  /**
   * Get array of all themes that are not disabled.
   *
   * @return array $themes
   */
  public function getThemes()
  {
    if ($this->_themes === null)
    {
      $themes = sfSympalConfig::get('themes', null, array());
      foreach ($themes as $name => $theme)
      {
        if (isset($theme['disabled']) && $theme['disabled'] === true)
        {
          continue;
        }
        $this->_themes[$name] = $theme;
      }
    }
    return $this->_themes;
  }

  /**
   * Get array of all themes that are not disabled and available for selection
   *
   * @return array $availableThemes
   */
  public function getAvailableThemes()
  {
    if ($this->_availableThemes === null)
    {
      $themes = $this->getThemes();
      foreach ($themes as $name => $theme)
      {
        if (!isset($theme['available']) || (isset($theme['available']) && $theme['available'] === false))
        {
          continue;
        }
        $this->_availableThemes[$name] = $theme;
      }
    }
    return $this->_availableThemes;
  }

  /**
   * Get array of configured content templates for a given moel name
   *
   * @param string $model
   * @return array $contentTemplates
   */
  public function getContentTemplates($model)
  {
    return sfSympalConfig::get($model, 'content_templates', array());
  }

  /**
   * Check if we are inside an admin module
   *
   * @return boolean
   */
  public function isAdminModule()
  {
    if (!$this->_symfonyContext)
    {
      return false;
    }
    $module = $this->_symfonyContext->getRequest()->getParameter('module');
    $adminModules = sfSympalConfig::get('admin_modules');
    return array_key_exists($module, $adminModules);
  }

  /**
   * Get the theme to use for the current request
   *
   * @return string $theme
   */
  public function getThemeForRequest()
  {
    $request = $this->_symfonyContext->getRequest();
    $module = $request->getParameter('module');

    if ($this->isAdminModule())
    {
      return sfSympalConfig::get('admin_theme', null, 'admin');
    }

    if (sfSympalConfig::get('allow_changing_theme_by_url'))
    {
      $user = $this->_symfonyContext->getUser();

      if ($theme = $request->getParameter(sfSympalConfig::get('theme_request_parameter_name', null, 'sf_sympal_theme')))
      {
        $user->setCurrentTheme($theme);
        return $theme;
      }

      if ($theme = $user->getCurrentTheme())
      {
        return $theme;
      }
    }

    if ($theme = sfSympalConfig::get($module, 'theme'))
    {
      return $theme;
    }

    if ($theme = $theme = sfSympalConfig::get(sfContext::getInstance()->getRouting()->getCurrentRouteName(), 'theme'))
    {
      return $theme;
    }

    return sfSympalConfig::get('default_theme');
  }

  /**
   * Initialize the theme for the current request
   *
   * @return void
   */
  public function initializeTheme()
  {
    if (!$this->_symfonyContext->getRequest()->isXmlHttpRequest())
    {
      $this->_sympalContext->loadTheme($this->getThemeForRequest());
    }
  }

  /**
   * Get the active sfSympalConfiguration instance
   *
   * @return sfSympalConfiguration $sympalConfiguration
   */
  public static function getActive()
  {
    return sfApplicationConfiguration::getActive()->getPluginConfiguration('sfSympalPlugin')->getSympalConfiguration();
  }
}