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
  protected
    $_sympalContext;

  /**
   * Externals plugins that are dependencies
   */
  public static $dependencies = array(
    'sfDoctrineGuardPlugin',
    'sfFormExtraPlugin',
    'sfTaskExtraPlugin',
    'sfFeed2Plugin',
    'sfWebBrowserPlugin',
    'sfImageTransformPlugin',
    'sfInlineObjectPlugin',
    'sfThemePlugin',
    'sfContentFilterPlugin',
    'sfSympalFormPlugin',
  );

  /**
   * sfSympalPlugin version number
   */
  const VERSION = '1.0.0-ALPHA5';

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
    $this->_checkDependencies();
    
    $this->_sympalConfiguration = new sfSympalConfiguration($this->configuration);

    // Actually bootstrap sympal
    $this->dispatcher->connect('context.load_factories', array($this, 'bootstrapContext'));

    self::_markClassesAsSafe();

    // Connect to the sympal post-load event
    $this->dispatcher->connect('sympal.load', array($this, 'configureSympal'));

    // Connect to the sympal.load_admin_menu event
    $this->dispatcher->connect('sympal.load_admin_menu', array($this, 'setupAdminMenu'));

    // Connect with theme.filter_asset_paths to rewrite asset paths from theme
    $this->dispatcher->connect('theme.filter_asset_paths', array($this, 'filterThemeAssetPaths'));
    
    /*
     * Initialize some symfony config.
     * 
     * Must be here (and not as a listener to sympal.load) so that it acts
     * before the theme manager has a chance to set any themes
     */
    $this->_initializeSymfonyConfig();
  }


  /**
   * Returns the sfSympalConfiguration object
   * 
   * @return sfSympalConfiguration
   */
  public function getSympalConfiguration()
  {
    return $this->_sympalConfiguration;
  }

  /**
   * Listens to the context.load_factories event and creates the sympal context
   */
  public function bootstrapContext(sfEvent $event)
  {
    $this->_sympalContext = sfSympalContext::createInstance($event->getSubject(), $this->getSympalConfiguration());
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
    
    $this->_configureSuperCache();

    // For BC with context->getSite();
    $this->dispatcher->connect('sympal.context.method_not_found', array($this, 'handleContextMethodNotFound'));

    // Add listener on template.filter_parameters to add sf_sympal_site var to view
    $site = $this->_sympalContext->getService('site_manager');
    $this->dispatcher->connect('template.filter_parameters', array($site, 'filterTemplateParameters'));
  }

  /**
   * Listens to the sympal.load_admin_menu to configure the admin menu
   */
  public function setupAdminMenu(sfEvent $event)
  {
    $menu = $event->getSubject();
    
    // Setup the Content menu
    $manageContent = $menu->getChild('content');
    $manageContent->setLabel('Content');

    $manageContent->addChild('Search', '@sympal_admin_search');

    $contentTypes = Doctrine_Core::getTable('sfSympalContentType')->getAllContentTypes();
    foreach ($contentTypes as $contentType)
    {
      $manageContent
        ->addChild($contentType->getLabel(), '@sympal_content_list_type?type='.$contentType->getId())
        ->setCredentials(array('ManageContent'));
    }

    $manageContent
      ->addChild('Slots', '@sympal_content_slots')
      ->setCredentials(array('ManageSlots'));

    $manageContent
      ->addChild('XML Sitemap', '@sympal_sitemap')
      ->setCredentials(array('ViewXmlSitemap'));


    // Setup the Site Administration menu
    $siteAdministration = $menu->getChild('site_administration');
    $siteAdministration->setLabel('Site Administration');

    $siteAdministration
      ->addChild('404 Redirects', '@sympal_redirects')
      ->setCredentials(array('ManageRedirects'));

    $siteAdministration
      ->addChild('Edit Site', '@sympal_sites_edit?id='.sfSympalContext::getInstance()->getService('site_manager')->getSite()->getId())
      ->setCredentials(array('ManageSites'));


    // Add to the Administration menu
    $administration = $menu->getChild('administration');

    $administration->addChild('Content Types', '@sympal_content_types')
      ->setCredentials(array('ManageContentTypes'));

    $administration->addChild('Sites', '@sympal_sites')
      ->setCredentials(array('ManageSites'));


    // Add a Content menu if applicable
    $content = $this->_sympalContext->getService('site_manager')->getCurrentContent();
    if ($content)
    {
      $contentEditor = $menu->getChild($content->getType()->slug);
      $contentEditor->setLabel($content->getType()->getLabel() . ' Actions');

      // If in the admin, put a link to view the content
      if (sfSympalConfiguration::getActive()->isAdminModule())
      {
        $contentEditor->addChild(__('View ').$content->getType()->getLabel(), $content->getRoute());    
      }
      
      $contentEditor
        ->addChild(__('Create New ').$content->getType()->getLabel(), '@sympal_content_create_type?type='.$content['Type']['slug'])
        ->setCredentials('ManageContent');

      $contentEditor
        ->addChild(__('Edit ').$content->getType()->getLabel(), $content->getEditRoute())
        ->setCredentials('ManageContent');

      $contentEditor
        ->addChild(__('Edit Content Type'), '@sympal_content_types_edit?id='.$content->getType()->getId())
        ->setCredentials('ManageMenus');

      // Add a menu item entry
      $menuItem = $this->_sympalContext->getService('menu_manager')->getCurrentMenuItem();
      if ($menuItem && $menuItem->exists())
      {
        $contentEditor
          ->addChild(__('Edit Menu Item'), '@sympal_content_menu_item?id='.$content->getId())
          ->setCredentials('ManageMenus');  
      }
      else
      {
        $contentEditor
          ->addChild(__('Add to Menu'), '@sympal_content_menu_item?id='.$content->getId())
          ->setCredentials('ManageMenus');
      }

      // Add publish/unpublish icons
      $user = sfContext::getInstance()->getUser();
      if($user->hasCredential('PublishContent'))
      {
        if($content->getIsPublished())
        {
          $contentEditor
            ->addChild(__('Unpublish'), '@sympal_unpublish_content?id='.$content->id, 'title='.__('Published on %date%', array('%date%' => format_date($content->getDatePublished(), 'g'))).'. '.__('Click to unpublish content.'));
        }
        elseif($content->getIsPublishInTheFuture())
        {
          $contentEditor
            ->addChild(__('Unpublish'), '@sympal_unpublish_content?id='.$content->id, 'title='.__('Will publish on %date%', array('%date%' => format_date($content->getDatePublished(), 'g'))).'. '.__('Click to unpublish content.'));
        }
        else
        {
          $contentEditor
            ->addChild(__('Publish'), '@sympal_publish_content?id='.$content->id, 'title='.__('Has not been published yet. '.__('Click to publish content.')));
        }
      }

      if (sfSympalConfig::isI18nEnabled())
      {
        foreach (sfSympalConfig::getLanguageCodes() as $code)
        {
          if (sfContext::getInstance()->getUser()->getEditCulture() != $code)
          {
            $contentEditor->addChild(__('Edit ').format_language($code), '@sympal_change_edit_language?language='.$code, 'title='.__('Switch to ').''.format_language($code));
          }
        }
      }
    }
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
   * Listens to the theme.filter_asset_paths event and rewrites all of
   * the assets from themes before outputting them
   */
  public function filterThemeAssetPaths(sfEvent $event, $assets)
  {
    foreach ($assets as $key => $asset)
    {
      $assets[$key]['file'] = sfSympalConfig::getAssetPath($asset['file']);
    }
    
    return $assets;
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

    sfConfig::set('app_sf_guard_plugin_success_signin_url', sfSympalConfig::get('success_signin_url'));

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
  }

  /**
   * Mark necessary Sympal classes as safe
   * 
   * These classes won't be wrapped with the output escaper
   * 
   * @todo Put the rest of these in the correct plugin
   *
   * @return void
   */
  private static function _markClassesAsSafe()
  {
    sfOutputEscaper::markClassesAsSafe(array(
      'sfSympalContent',
      'sfSympalContentTranslation',
      'sfSympalContentSlot',
      'sfSympalContentSlotTranslation',
      'sfSympalContentRenderer',
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
   * Checks to see if all of sympal's plugin dependencies are met
   *
   * @return void
   * @throws sfException
   */
  protected function _checkDependencies()
  {
    $unmet = array_diff(self::$dependencies, $this->configuration->getPlugins());

    if (count($unmet) > 0)
    {
      throw new sfException(
        'The following plugins must be installed and enabled for sympal to function: '. implode(', ', $unmet)
      );
    }
  }

  /**
   * Array of all the core Sympal plugins
   * 
   * A core plugin is one that lives in the lib/plugins directory of sfSympalPlugin.
   * A core plugin will be enabled automatically
   */
  public static $corePlugins = array(
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
    'sfSympalSearchPlugin',
  );
}
