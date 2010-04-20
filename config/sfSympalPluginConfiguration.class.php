<?php

/**
 * Main Plugin configuration class for sympal.
 * 
 * This is responsible for loading in plugins that are core to sympal
 * 
 * @package     sfSympalPlugin
 * @subpackage  config
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @since       2010-03-26
 * @version     svn:$Id$ $Author$
 */
class sfSympalPluginConfiguration extends sfPluginConfiguration
{
  /**
   * sfSympalPlugin version number
   */
  const VERSION = '1.0.0-ALPHA4';

  /**
   * Public reference to instanceof sfSympalConfiguration
   */
  public
    $sympalConfiguration;
  
  protected
    $_sympalContext;

  /**
   * Array of all the core Sympal plugins
   * 
   * A core plugin is one that lives in the lib/plugins directory of sfSympalPlugin.
   * A core plugin will be enabled automatically
   */
  public static
    $corePlugins = array(
      'sfDoctrineGuardPlugin',
      'sfFormExtraPlugin',
      'sfTaskExtraPlugin',
      'sfFeed2Plugin',
      'sfWebBrowserPlugin',
      'sfJqueryReloadedPlugin',
      'sfImageTransformPlugin',
      'sfSympalCMFPlugin',
      'sfSympalMenuPlugin',
      'sfSympalPluginManagerPlugin',
      'sfSympalPagesPlugin',
      'sfSympalContentListPlugin',
      'sfSympalDataGridPlugin',
      'sfSympalUserPlugin',
      'sfSympalInstallPlugin',
      'sfSympalUpgradePlugin',
      'sfSympalRenderingPlugin',
      'sfSympalAdminPlugin',
      'sfSympalEditorPlugin',
      'sfSympalAssetsPlugin',
      'sfSympalContentSyntaxPlugin',
      'sfSympalSearchPlugin',
      'sfSympalThemePlugin',
      'sfSympalMinifyPlugin',
      'sfSympalFormPlugin',
    );

  /**
   * Shortcut method to enable all Sympal plugins for the given ProjectConfiguration
   *
   * Returns an instance of sfSympalPluginEnabler which allows you to enable
   * disable and override any plugins with a convenient API.
   *
   * @param ProjectConfiguration $configuration
   * @return sfSympalPluginEnabler $enabler  Instance of sfSympalPluginEnabler
   */
  public static function enableSympalPlugins(ProjectConfiguration $configuration)
  {
    require_once(dirname(__FILE__).'/../lib/sfSympalPluginEnabler.class.php');

    $enabler = new sfSympalPluginEnabler($configuration);
    $enabler->enableSympalPlugins();

    return $enabler;
  }

  /**
   * sfSympalPluginConfiguration initialize() method instantiates the
   * sfSympalConfiguration instance for the current symfony dispatcher
   * and configuration
   */
  public function initialize()
  {
    // extend sfSympalConfiguration via sfSympalContentConfiguration
    $configuration = new sfSympalContentConfiguration();
    $this->dispatcher->connect('sympal.configuration.method_not_found', array($configuration, 'extend'));

    // Listen to the sympal post-load event
    $this->dispatcher->connect('sympal.load', array($this, 'configureSympal'));
  }

  /**
   * Listens to the sympal.load event
   */
  public function configureSympal(sfEvent $event)
  {
    $this->_sympalContext = $event->getSubject();
    
    // @todo this should be broken up, possibly moved, removed
    $this->configuration->loadHelpers(array(
      'Sympal',
      'I18N',
      'Asset',
      'Url',
      'Partial',
      'SympalContentSlot',
      'SympalPager',
    ));
    
    $this->_initializeSymfonyConfig();
    $this->_markClassesAsSafe();
    $this->_configureSuperCache();
    
    $this->dispatcher->connect('sympal.context.method_not_found', array($this, 'handleContextMethodNotFound'));
  }

  /**
   * Listener on the sympal.context.method_not_found event. 
   * 
   * Extends the sfSympalContext class. This handles
   *   * ->getSite()
   */
  public function handleContextMethodNotFound(sfEvent $event)
  {
    if ($event['method'] == 'getSite')
    {
      $event->setReturnValue($this->_sympalContext->getService('site_manager')->getSite());
      
      return true;
    }
    
    return false;
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
      sfConfig::set('sf_login_module', 'sympal_admin');
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
   * These classes won't be wrapped with the output escaper
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
      $superCache = new sfSympalSuperCache();
      $this->_dispatcher->connect('response.filter_content', array($superCache, 'listenToResponseFilterContent'));
    }
  }

  /**
   * Shortcut convenience method to get the current instance of sfSympalConfiguration
   *
   * @return sfSympalConfiguration $configuration
   */
  public function getSympalConfiguration()
  {
    return $this->sympalConfiguration;
  }
}
