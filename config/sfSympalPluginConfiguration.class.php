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
   * sfSympalPlugin version number
   */
  const VERSION = '1.0.0-ALPHA4';

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
    self::_markClassesAsSafe();
    
    // extend sfSympalConfiguration via sfSympalContentConfiguration
    $configuration = new sfSympalContentConfiguration();
    $this->dispatcher->connect('sympal.configuration.method_not_found', array($configuration, 'extend'));

    // Connect to the sympal post-load event
    $this->dispatcher->connect('sympal.load', array($this, 'configureSympal'));

    // Connect to the sympal.load_admin_menu event
    $this->dispatcher->connect('sympal.load_admin_menu', array($this, 'setupAdminMenu'));
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
    $this->_configureSuperCache();
    
    $this->dispatcher->connect('sympal.context.method_not_found', array($this, 'handleContextMethodNotFound'));
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
      'sfInlineObjectPlugin',
      'sfSympalCorePlugin',
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
      'sfSympalThemePlugin',
      'sfSympalMinifyPlugin',
      'sfSympalFormPlugin',
    );
}
