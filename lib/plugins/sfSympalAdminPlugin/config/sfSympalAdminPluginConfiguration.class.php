<?php

/**
 * Plugin configuration for the admin plugin
 * 
 * Hooks up to several events related to editors and menus
 * 
 * @package     sfSympalAdminPlugin
 * @subpackage  config
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @since       2010-03-27
 * @version     svn:$Id$ $Author$
 */
class sfSympalAdminPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    // Connect to the sympal.load_admin_menu event
    $this->dispatcher->connect('sympal.load_admin_menu', array($this, 'setupAdminMenu'));
    
    // Connect to the sympal.load_config_form evnet
    $this->dispatcher->connect('sympal.load_config_form', array($this, 'loadConfigForm'));
    
    // Connect to the sympal.load_editor event
    $this->dispatcher->connect('sympal.load_editor', array($this, 'loadEditor'));
    
    // Connect to the sympal.load event
    $this->dispatcher->connect('sympal.load', array($this, 'boostrap'));
    
    // Connect to the sympal.theme.set_theme_from_request to load the admin theme for admin modules
    $this->dispatcher->connect('sympal.theme.set_theme_from_request', array($this, 'setThemeForAdminModule'));
    
    // Connect to the sympal.configuration.method_not_found to extend sfSympalConfiguration
    $configuration = new sfSympalAdminConfiguration();
    $this->dispatcher->connect('sympal.configuration.method_not_found', array($configuration, 'extend'));
    
    // Connect to the component.method_not_found event to extend the actions class
    $actions = new sfSympalAdminActions();
    $this->dispatcher->connect('component.method_not_found', array($actions, 'extend'));
  }

  /**
   * Listens to the sympal.load event
   */
  public function boostrap()
  {
    $this->configuration->loadHelpers(array('SympalAdmin'));
    
    // load the admin menu
    if ($this->_shouldLoadAdminMenu())
    {
      $this->_loadAdminMenuAssets();

      $this->dispatcher->connect('response.filter_content', array($this, 'addAdminMenuHtml'));
    }
  }

  /**
   * Listens to the response.filter_content event and adds the
   * editor drop-down menu to the response
   */
  public function addAdminMenuHtml(sfEvent $event, $content)
  {
    // See if the editor was disabled
    if (!sfConfig::get('sympal.editor_menu', true))
    {
      return $content;
    }
    
    $statusCode = $event->getSubject()->getStatusCode();
    if ($statusCode == 404 || $statusCode == 500)
    {
      return $content;
    }
    
    $this->configuration->loadHelpers(array('SympalAdmin'));
    $content = str_replace('</body>', get_sympal_admin_menu().'</body>', $content);

    return $content;
  }

  /**
   * Listens to sympal.load_admin_menu and configures the admin menu
   */
  public function setupAdminMenu(sfEvent $event)
  {
    $menu = $event->getSubject();
    $user = sfContext::getInstance()->getUser();

    // Setup Change language menu
    if (sfSympalConfig::isI18nEnabled())
    {
      $this->configuration->loadHelpers(array('Partial', 'I18N'));
      $changeLanguage = $menu->getChild('change_language');
      $changeLanguage->setLabel('Change Language');
      $currentCulture = strtolower($user->getCulture());
      $codes = sfSympalConfig::getLanguageCodes();
      foreach ($codes as $code)
      {
        $code = strtolower($code);
        $formatted = format_language($code);
        if (!$formatted)
        {
          $formatted = format_language($code, 'en');
        }
        if ($formatted)
        {
          $changeLanguage->addChild(ucwords($formatted), '@sympal_change_language?language='.$code, 'title='.__('Switch to ').''.$formatted);
        }
      }
    }

    $administration = $menu->getChild('administration');
    $administration->setLabel('Administration');

    $administration->addChild('System Settings', '@sympal_config')
      ->setCredentials(array('ManageSystemSettings'));

    $administration->addChild('Check Server', '@sympal_check_server')
      ->setCredentials(array('ViewServerCheck'));
  }

  public function loadConfigForm(sfEvent $event)
  {
    $form = $event->getSubject();
    $form->addSetting(null, 'rows_per_page', 'Rows Per Page');

    if (sfSympalConfig::isI18nEnabled())
    {
      $cultures = sfCultureInfo::getCultures(sfCultureInfo::NEUTRAL);
      $languages = array();
      foreach ($cultures as $key => $value)
      {
        $formatted = format_language($value);
        if (!$formatted)
        {
          $formatted = format_language($value, 'en');
        }
        if ($formatted)
        {
          $languages[$value] = $formatted;
        }
      }
      asort($languages);
      $widget = new sfWidgetFormChoice(array('multiple' => true, 'choices' => $languages));
      $validator = new sfValidatorChoice(array('multiple' => true, 'choices' => array_keys($languages)));
      $form->addSetting(null, 'language_codes', 'Available Cultures', $widget, $validator);

      $languageForm = new sfFormLanguage(
        sfContext::getInstance()->getUser(), 
        array('languages' => sfSympalConfig::getLanguageCodes())
      );
      $widgetSchema = $languageForm->getWidgetSchema();
      $validatorSchema = $languageForm->getValidatorSchema();

      $form->addSetting(null, 'default_culture', 'Default Culture', $widgetSchema['language'], $validatorSchema['language']);
    }

    $array = sfSympalContext::getInstance()->getService('theme_form_toolkit')->getThemeWidgetAndValidator();
    $form->addSetting('theme', 'default_theme', 'Default Theme', $array['widget'], $array['validator']);

    $form->addSetting(null, 'default_rendering_module', 'Default Rendering Module');
    $form->addSetting(null, 'default_rendering_action', 'Default Rendering Action');
    $form->addSetting(null, 'recaptcha_public_key', 'Recaptcha Public Key');
    $form->addSetting(null, 'recaptcha_private_key', 'Recaptcha Private Key');
    $form->addSetting(null, 'breadcrumbs_separator', 'Breadcrumbs Separator');
    $form->addSetting(null, 'default_from_email_address', 'Default From Address');
    $form->addSetting(null, 'enable_markdown_editor', 'Enable Markdown Editor', 'InputCheckbox', 'Boolean');
    $form->addSetting(null, 'elastic_textareas', 'Elastic Textareas', 'InputCheckbox', 'Boolean');
    $form->addSetting(null, 'check_for_upgrades_on_dashboard', 'Check for Upgrades', 'InputCheckbox', 'Boolean');

    $form->addSetting('plugin_api', 'username', 'Username or API Key');
    $form->addSetting('plugin_api', 'password');

    $form->addSetting('page_cache', 'enabled', 'Enabled?', 'InputCheckbox', 'Boolean');

    $form->addSetting('page_cache', 'super', 'Enable Super Cache?', 'InputCheckbox', 'Boolean');
    $form->addSetting('page_cache', 'with_layout', 'With layout?', 'InputCheckbox', 'Boolean');
    $form->addSetting('page_cache', 'lifetime', 'Lifetime');
  }

  public function loadEditor(sfEvent $event)
  {
    $user = sfContext::getInstance()->getUser();

    $this->configuration->loadHelpers(array('Asset', 'Partial', 'I18N'));

    $menu = $event->getSubject();
    $content = $event['content'];
    $menuItem = $event['menuItem'];

    $sympalConfiguration = sfSympalConfiguration::getActive();
    $user = sfContext::getInstance()->getUser();
    $request = sfContext::getInstance()->getRequest();

    $contentEditor = $menu->getChild($content->getType()->getLabel() . ' Actions');

    if ($sympalConfiguration->isAdminModule())
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

    if ($menuItem && $menuItem->exists())
    {
      $contentEditor
        ->addChild(__('Edit Menu Item'), '@sympal_content_menu_item?id='.$content->getId())
        ->setCredentials('ManageMenus');  
    } else {
      $contentEditor
        ->addChild(__('Add to Menu'), '@sympal_content_menu_item?id='.$content->getId())
        ->setCredentials('ManageMenus');
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

    if($user->hasCredential('PublishContent'))
    {
      if($content->getIsPublished())
      {
        $contentEditor
          ->addChild(__('Unpublish'), '@sympal_unpublish_content?id='.$content['id'], 'title='.__('Published on %date%', array('%date%' => format_date($content->getDatePublished(), 'g'))).'. '.__('Click to unpublish content.'));
      }
      elseif($content->getIsPublishInTheFuture())
      {
        $contentEditor
          ->addChild(__('Unpublish'), '@sympal_unpublish_content?id='.$content['id'], 'title='.__('Will publish on %date%', array('%date%' => format_date($content->getDatePublished(), 'g'))).'. '.__('Click to unpublish content.'));
      }
      else
      {
        $contentEditor
          ->addChild(__('Publish'), '@sympal_publish_content?id='.$content['id'], 'title='.__('Has not been published yet. '.__('Click to publish content.')));
      }
    } 
  }

  /**
   * Listens to the sympal.theme.set_theme_from_request event and sets the
   * theme to the admin theme if the current module is an admin module
   */
  public function setThemeForAdminModule(sfEvent $event)
  {
    $module = $event['context']->getRequest()->getParameter('module');
    $adminModules = sfSympalConfig::get('admin_modules', null, array());
    
    if (array_key_exists($module, $adminModules))
    {
      $event->setReturnValue(sfSympalConfig::get('admin_theme', null, 'admin'));
      
      return true; // Set the event as processed
    }
    
    return false; // Set the event as not processed
  }

  /**
   * Determins whether the admin menu should be loaded based on credentials
   * and the type of request
   * 
   * @return boolean
   */
  protected function _shouldLoadAdminMenu()
  {
    $context = sfContext::getInstance();
    $request = $context->getRequest();
    $format = $request->getRequestFormat();
    $format = $format ? $format : 'html';

    return $context->getUser()->hasCredential('ViewAdminBar')
      && $format == 'html'
      && $request->getParameter('module') !== 'sympal_dashboard';
  }

  /**
   * Called when the admin menu is loaded.
   * 
   * Supplies all of the css and js needed for the admin menu
   */
  protected function _loadAdminMenuAssets()
  {
    sfSympalToolkit::useJQuery();

    $response = sfContext::getInstance()->getResponse();
    $response->addStylesheet(sfSympalConfig::getAssetPath('/sfSympalAdminPlugin/css/menu.css'));

    $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalAdminPlugin/js/menu.js'));
    $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalPlugin/js/shortcuts.js'));
    $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalAdminPlugin/js/shortcuts.js'));

    $response->addStylesheet(sfSympalConfig::getAssetPath('/sfSympalPlugin/fancybox/jquery.fancybox.css'));
    $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalPlugin/fancybox/jquery.fancybox.js'));
  }
}
