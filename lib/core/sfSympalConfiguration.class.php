<?php

class sfSympalConfiguration
{
  protected
    $_dispatcher,
    $_projectConfiguration,
    $_sympalContext,
    $_bootstrap,
    $_plugins = array(),
    $_modules = array(),
    $_layouts = array();

  public function __construct(sfEventDispatcher $dispatcher, ProjectConfiguration $projectConfiguration)
  {
    $this->_dispatcher = $dispatcher;
    $this->_projectConfiguration = $projectConfiguration;

    $this->_initializeSymfonyConfig();

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
      'sfSympalUpgradeFromWeb'
    ));

    $this->_dispatcher->connect('context.load_factories', array($this, 'bootstrap'));
    $this->_dispatcher->connect('component.method_not_found', array(new sfSympalActions(), 'extend'));
    $this->_dispatcher->connect('controller.change_action', array($this, 'initializeTheme'));
    $this->_dispatcher->connect('template.filter_parameters', array($this, 'filterTemplateParameters'));
    $this->_dispatcher->connect('form.method_not_found', array(new sfSympalForm(), 'extend'));
    $this->_dispatcher->connect('form.post_configure', array('sfSympalForm', 'listenToFormPostConfigure'));
    $this->_dispatcher->connect('form.filter_values', array('sfSympalForm', 'listenToFormFilterValues'));
    $this->_dispatcher->connect('routing.load_configuration', array($this, 'listenToRoutingLoadConfiguration'));

    if (sfSympalConfig::get('page_cache', 'super') && sfConfig::get('sf_cache'))
    {
      $superCache = new sfSympalSuperCache($this);
      $this->_dispatcher->connect('response.filter_content', array($superCache, 'listenToResponseFilterContent'));
      $this->_dispatcher->connect('task.cache.clear', array($superCache, 'listenToTaskCacheClear'));
    }

    Doctrine_Manager::getInstance()->setAttribute(Doctrine_Core::ATTR_HYDRATE_OVERWRITE, false);
    Doctrine_Manager::getInstance()->setAttribute(Doctrine_Core::ATTR_TABLE_CLASS, 'sfSympalDoctrineTable');
    Doctrine_Manager::getInstance()->setAttribute(Doctrine_Core::ATTR_QUERY_CLASS, 'sfSympalDoctrineQuery');
    Doctrine_Manager::getInstance()->setAttribute(Doctrine_Core::ATTR_COLLECTION_CLASS, 'sfSympalDoctrineCollection');
  }

  public function listenToRoutingLoadConfiguration(sfEvent $event)
  {
    // Append route at end to catch all
    $event->getSubject()->appendRoute('sympal_default', new sfDoctrineRoute('/:sympal_content_slug/*', array(
      'module' => 'sympal_content_renderer',
      'action' => 'index',
      'result_type' => 'object'
    ), array(
      'model' => 'sfSympalContent',
    ),array(
      'allow_empty' => true,
      'model' => 'sfSympalContent',
      'type' => 'object',
      'method' => 'getContent'
    )));
  }

  /**
   * Callable attached to Symfony event context.load_factories. When this event
   * is triggered we also create the Sympal context.
   */
  public function bootstrap(sfEvent $event)
  {
    $record = Doctrine_Core::getTable('sfGuardUser')->getRecordInstance();
    $this->_dispatcher->notify(new sfEvent($record, 'sympal.user.set_table_definition', array('object' => $record)));

    $this->_cache = new sfSympalCache($this);

    $this->_sympalContext = sfSympalContext::createInstance($event->getSubject());

    $this->_enableModules();

    $this->_redirectIfNotInstalled();

    $this->initializeTheme();

    $this->_projectConfiguration->loadHelpers(array(
      'Sympal', 'SympalContentSlot', 'SympalMenu', 'SympalPager', 'I18N'
    ));
  }

  public function filterTemplateParameters(sfEvent $event, $parameters)
  {
    $parameters['sf_sympal_context'] = $this->_sympalContext;
    return $parameters;
  }

  public function getCache()
  {
    return $this->_cache;
  }

  public function getSympalContext()
  {
    return $this->_sympalContext;
  }

  public function getContentTypes()
  {
    return $this->_cache->getContentTypes();
  }

  public function getProjectConfiguration()
  {
    return $this->_projectConfiguration;
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

    return array_values(array_unique($requiredPlugins));
  }

  public function getCorePlugins()
  {
    return sfSympalPluginConfiguration::$dependencies;
  }

  public function getContentTypePlugins()
  {
    $contentTypePlugins = array();
    $plugins = $this->getPluginPaths();

    foreach ($plugins as $plugin => $path)
    {
      $manager = new sfSympalPluginManager($plugin, $this->_projectConfiguration, new sfFormatter());
      if ($contentType = $manager->getContentTypeForPlugin())
      {
        $contentTypePlugins[$plugin] = $plugin;
      }
    }
    return $contentTypePlugins;
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
    return $this->getCache()->getModules();
  }

  public function getLayouts()
  {
    return $this->getCache()->getLayouts();
  }

  public function getDefaultTheme(sfWebRequest $request)
  {
    $theme = sfSympalConfig::get($request->getParameter('module'), 'theme');
    if (!$theme)
    {
      $theme = sfSympalConfig::get(sfContext::getInstance()->getRouting()->getCurrentRouteName(), 'theme');
    }
    if (!$theme)
    {
      $theme = sfSympalConfig::get('default_theme');
    }
    return $theme;
  }

  public function initializeTheme()
  {
    $request = sfContext::getInstance()->getRequest();

    if (!$request->isXmlHttpRequest() && $request->getParameter('module') != 'sympal_content_renderer')
    {
      $this->_sympalContext->loadTheme($this->getDefaultTheme($request));
    }
  }

  public function checkPluginDependencies()
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

  private function _enableModules()
  {
    if (sfSympalConfig::get('enable_all_modules', null, true))
    {
      $modules = sfConfig::get('sf_enabled_modules', array());
      if (sfSympalConfig::get('enable_all_modules'))
      {
        $modules = array_merge($modules, $this->getModules());
      }
      $modules = array_merge($modules, sfSympalConfig::get('enabled_modules', null, array()));

      sfConfig::set('sf_enabled_modules', $modules);
    }
  }

  private function _redirectIfNotInstalled()
  {
    $sfContext = sfContext::getInstance();
    $request = $sfContext->getRequest();

    // Redirect to install module if...
    //  not in test environment
    //  sympal has not been installed
    //  module is not already sympal_install
    if (sfConfig::get('sf_environment') != 'test' && !sfSympalConfig::get('installed') && $request->getParameter('module') != 'sympal_install')
    {
      $sfContext->getController()->redirect('@sympal_install');
    }
  }

  private function _initializeSymfonyConfig()
  {
    sfConfig::set('sf_cache', sfSympalConfig::get('page_cache', 'enabled', false));
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
}