<?php

/**
 * Configuration class for the core CMF plugin for Sympal
 * 
 * @package     sfSympalCMFPlugin
 * @subpackage  config
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @since       2010-03-28
 * @version     svn:$Id$ $Author$
 */
class sfSympalCMFPluginConfiguration extends sfPluginConfiguration
{
  protected
    $_sympalContext;
  
  public function initialize()
  {
    $this->dispatcher->connect('sympal.load', array($this, 'configureSympal'));
    
    // extend sfSympalConfiguration via sfSympalContentConfiguration
    $configuration = new sfSympalContentConfiguration();
    $this->dispatcher->connect('sympal.configuration.method_not_found', array($configuration, 'extend'));
  }

  /**
   * Listens to sympal.load and performs configuration on Sympal
   * 
   * @see sfSympalConfiguration
   */
  public function configureSympal(sfEvent $event)
  {
    $this->_sympalContext = $event->getSubject();
    
    $this->_initializeSymfonyConfig();
    $this->_markClassesAsSafe();
    $this->_configureSuperCache();
    
    $this->_sympalContext->getApplicationConfiguration()->loadHelpers(array(
      'SympalContentSlot',
      'SympalPager',
    ));
    
    $this->dispatcher->connect('template.filter_parameters', array($this, 'listenTemplateFilterParameters'));
    $this->dispatcher->connect('sympal.context.method_not_found', array($this, 'handleContextMethodNotFound'));
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
   * Listens to template.filter_parameters and adds a few variables to the view
   */
  public function listenTemplateFilterParameters(sfEvent $event, $parameters)
  {
    $siteManager = $this->_sympalContext->getService('site_manager');

    $parameters['sf_sympal_site'] = $siteManager->getSite();
    
    if ($content = $siteManager->getCurrentContent())
    {
      $parameters['sf_sympal_content'] = $content;
    }
    
    return $parameters;
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
}