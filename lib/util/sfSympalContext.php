<?php

/**
 * Context class for a Sympal instance
 * 
 * A Sympal "context" is a singleton with respect to an individual sfSympalSite
 * record. This is very similar to sfContext, which is a singleton with respect
 * to each symfony app.
 * 
 * If some object has a dependency on a symfony app but NOT an sfSympalSite
 * record, then it should be handled by sfContext. If it DOES have a
 * dependency on the current sfSympalSite record, it'll be handled here
 * on the sfSympalContext instance.
 * 
 * This manages things such as
 *   * The current sfSympalSite object
 *   * The current menu item
 *   * The current content object (sfSympalContent)
 * 
 * @package     sfSympalPlugin
 * @subpackage  util
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-03-27
 * @version     svn:$Id$ $Author$
 */
class sfSympalContext
{
  protected static
    $_instances = array(),
    $_current;
  
  protected
    $_dispatcher,
    $_sympalConfiguration,
    $_symfonyContext;
  
  protected
    $_currentMenuItem,
    $_currentContent;
  
  protected
    $_serviceContainer;

  /**
   * Class constructor
   * 
   * @param sfSympalConfiguration $sympalConfiguration The Sympal configuration
   * @param sfContext $symfonyContext The symfony context
   */
  public function __construct(sfSympalConfiguration $sympalConfiguration, sfContext $symfonyContext)
  {
    $this->_dispatcher = $symfonyContext->getEventDispatcher();
    $this->_sympalConfiguration = $sympalConfiguration;
    $this->_symfonyContext = $symfonyContext;
    
    $this->initialize();
  }

  /**
   * Initializes sympal
   */
  protected function initialize()
  {
    // load the service container instance and then configure it
    $this->loadServiceContainer();
    $this->configureServiceContainer();

    // enable modules based on sympal configuration
    $this->_enableModules();

    // register some listeners
    $this->_registerExtendingClasses();
    $this->_registerListeners();

    // notify that sympal is done bootstrapping
    $this->_dispatcher->notify(new sfEvent($this, 'sympal.load', array()));
  }

  /**
   * Registers certain classes that extend core symfony classes
   */
  protected function _registerExtendingClasses()
  {
    // extend the component/action class
    $actions = $this->getServiceContainer()->getService('actions_extended');
    $this->_dispatcher->connect('component.method_not_found', array($actions, 'extend'));
    
    // extend the form class
    $form = $this->getServiceContainer()->getService('form_extended');
    $this->_dispatcher->connect('form.method_not_found', array($form, 'extend'));
  }

  /**
   * Registeres needed event listeners
   */
  protected function _registerListeners()
  {
    // The controller.change_action event
    new sfSympalControllerChangeActionListener($this->_dispatcher, $this);

    // The template.filter_parameters event
    new sfSympalTemplateFilterParametersListener($this->_dispatcher, $this);

    // The form.post_configure event
    new sfSympalFormPostConfigureListener($this->_dispatcher, $this);

    // The form.filter_values event
    new sfSympalFormFilterValuesListener($this->_dispatcher, $this);
  }

  /**
   * Loads Sympal's service container
   * 
   * @link http://components.symfony-project.org/dependency-injection/trunk/book/06-Speed
   */
  protected function loadServiceContainer()
  {
    $autoloaderPath = $this->getSymfonyContext()
      ->getConfiguration()
      ->getPluginConfiguration('sfSympalPlugin')
      ->getRootDir() . '/lib/vendor/service_container/lib/sfServiceContainerAutoloader.php';
    
    if (!file_exists($autoloaderPath))
    {
      throw new sfException(sprintf(
        'Cannot find the service container library at %s.
        
        If you are including sfSympalPlugin as a git submodule, be sure to run the following commands from inside the plugins/sfSympalPlugin directory:
        
         git submodule init
         git submodule update',
        $autoloaderPath
      ));
    }
    
    sfServiceContainerAutoloader::register();
    
    $app = $this->getSymfonyContext()->getConfiguration()->getApplication();
    $name = 'sfSympal'.$app.'ServiceContainer';
    $path = sfConfig::get('sf_app_cache_dir').'/'.$name.'.php';

    if (!sfConfig::get('sf_debug') && file_exists($path))
    {
      require_once $path;
      $this->_serviceContainer = new $name();
    }
    else
    {
      // build the service container dynamically
      $this->_serviceContainer = new sfServiceContainerBuilder();
      $loader = new sfServiceContainerLoaderFileYaml($this->_serviceContainer);
      
      $configPaths = $this->getSymfonyContext()->getConfiguration()->getConfigPaths('config/sympal_services.yml');
      $loader->load($configPaths);
      
      // if not in debug, write the service container to file
      if (!sfConfig::get('sf_debug'))
      {
        $dumper = new sfServiceContainerDumperPhp($this->_serviceContainer);

        file_put_contents($path, $dumper->dump(array(
          'class'       => $name,
          'base_class'  => sfSympalConfig::get('service_container', 'base_class', 'sfServiceContainer'),
        )));
      }
    }
  }

  /**
   * Configures the service container.
   * 
   * This adds services (both symfony and Sympal services) needed in
   * the service container
   */
  protected function configureServiceContainer()
  {
    $sc = $this->getServiceContainer();
    $context = $this->getSymfonyContext();
    
    $sc->setService('dispatcher',       $context->getEventDispatcher());
    $sc->setService('user',             $context->getUser());
    $sc->setService('response',         $context->getResponse());
    $sc->setService('logger',           $context->getLogger());
    $sc->setService('config_cache',     $context->getConfigCache());
    $sc->setService('controller',       $context->getController());
    $sc->setService('request',          $context->getRequest());
    $sc->setService('routing',          $context->getRouting());
    if (sfConfig::get('sf_i18n'))
    {
      $sc->setService('i18n',             $context->getI18n());
    }
    
    $sc->setService('context',          $context);
    
    $sc->setService('sympal_configuration', $this->getSympalConfiguration());
    $sc->setService('sympal_context',       $this);
  }

  /**
   * Helper method to retrieve a service
   * 
   * @param string $name The name of the service to retrieve
   */
  public function getService($name)
  {
    return $this->getServiceContainer()->getService($name);
  }

  /**
   * Shortcut to check if we should load the frontend editor
   *
   * @return boolean
   */
  public function shouldLoadFrontendEditor()
  {
    return $this->_symfonyContext->getConfiguration()->getPluginConfiguration('sfSympalEditorPlugin')->shouldLoadEditor();
  }

  /**
   * Get the current sfSympalConfiguration instance
   *
   * @return sfSympalConfiguration $sympalConfiguration
   */
  public function getSympalConfiguration()
  {
    return $this->_sympalConfiguration;
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

  public function getApplicationConfiguration()
  {
    return $this->getSympalConfiguration()->getProjectConfiguration();
  }

  /**
   * Get a sfSympalContentRenderer instance for a given sfSympalContent instance
   *
   * @param sfSympalContent $content The sfSympalContent instance
   * @param string $format Optional format to render
   * @return sfSympalContentRenderer $renderer
   */
  public function getContentRenderer(sfSympalContent $content, $format = null)
  {
    return new sfSympalContentRenderer($this, $content, $format);
  }

  /**
   * Handle the enabling of modules.
   * 
   * Either enables all modules or only modules defined by enabled_modules.
   * In either case, the modules in disabled_modules are disabled
   *
   * @return void
   */
  private function _enableModules()
  {
    $modules = sfConfig::get('sf_enabled_modules', array());
    if (sfSympalConfig::get('enable_all_modules'))
    {
      $modules = array_merge($modules, $this->getSympalConfiguration()->getModules());
    }
    else
    {
      $modules = array_merge($modules, sfSympalConfig::get('enabled_modules', null, array()));
    }

    if ($disabledModules = sfSympalConfig::get('disabled_modules', null, array()))
    {
      $modules = array_diff($modules, $disabledModules);
    }

    sfConfig::set('sf_enabled_modules', $modules);
  }

  /**
   * Returns the service container instance
   * 
   * @return sfServiceContainer
   */
  public function getServiceContainer()
  {
    return $this->_serviceContainer;
  }

  /**
   * Get a sfSympalContext instance
   *
   * @param string $site Optional site/app name to get
   * @return sfSympalContext $sympalContext
   */
  public static function getInstance($site = null)
  {
    if (is_null($site))
    {
      if (!self::$_current)
      {
        throw new InvalidArgumentException('Could not find a current sympal context instance');
      }
      return self::$_current;
    }

    if (!isset(self::$_instances[$site]))
    {
      throw new sfException($site.' instance does not exist.');
    }
    return self::$_instances[$site];
  }

  /**
   * Check if we have a sfSympalContext yet
   *
   * @param string $site Optional site/app name to check for
   * @return boolean
   */
  public static function hasInstance($site = null)
  {
    return is_null($site) ? !empty(self::$_instances) : isset(self::$_instances[$site]);
  }

  /**
   * Create a new sfSympalContext instance for a given sfContext and sfSympalConfiguration instance
   *
   * @param sfContext $symfonyContext 
   * @param sfSympalConfiguration $sympalConfiguration 
   * @return sfSympalContext $sympalContext
   */
  public static function createInstance(sfContext $symfonyContext, sfSympalConfiguration $sympalConfiguration)
  {
    $name = $symfonyContext->getConfiguration()->getApplication();

    $instance = new self($sympalConfiguration, $symfonyContext);
    self::$_instances[$name] = $instance;
    self::$_current = $instance;

    return self::$_instances[$name];
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
    $event = $this->_dispatcher->notifyUntil(new sfEvent($this, 'sympal.context.method_not_found', array('method' => $method, 'arguments' => $arguments)));
    if (!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }

    return $event->getReturnValue();
  }

  /**
   * Deprecated Functions
   */
  
  protected function warnDeprecated($method, $service)
  {
    $this->_dispatcher->notify(new sfEvent($this, 'application.log', array(
      'priority' => sfLogger::WARNING,
      sprintf('Method sfSympalContent::%s is deprecated. Use sfSympalContext->getService(\'%s\')->%s()', $method, $service, $method),
    )));
  }
  
  /**
   * @deprecated
   */
  public function getCurrentContent()
  {
    $this->warnDeprecated('getCurrentContent', 'site_manager');
    
    return $this->getService('site_manager')->getCurrentContent();
  }
  /**
   * @deprecated
   */
  public function setCurrentContent(sfSympalContent $content)
  {
    $this->warnDeprecated('setCurrentContent', 'site_manager');
    
    return $this->getService('site_manager')->setCurrentContent($content);
  }
  
  /**
   * @deprecated
   */
  public function getCurrentMenuItem()
  {
    $this->warnDeprecated('getCurrentMenuItem', 'menu_manager');
    
    return $this->getService('menu_manager')->getCurrentMenuItem();
  }
  
}