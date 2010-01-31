<?php

class sfSympalAdminPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    $this->dispatcher->connect('sympal.load_admin_menu', array($this, 'loadAdminMenu'));
    $this->dispatcher->connect('sympal.load_config_form', array($this, 'loadConfigForm'));
    $this->dispatcher->connect('sympal.load_editor', array($this, 'loadEditor'));
    $this->dispatcher->connect('context.load_factories', array($this, 'addAdminMenu'));
    $this->dispatcher->connect('sympal.load_inline_edit_bar_buttons', array($this, 'loadInlineEditBarButtons'));
  }

  public function loadInlineEditBarButtons(sfEvent $event)
  {
    $menu = $event->getSubject();

    $menu->
      addChild('Dashboard', '@sympal_dashboard')->
      setInputClass('toggle_dashboard_menu')
    ;
  }

  public function addAdminMenu()
  {
    $format = sfContext::getInstance()->getRequest()->getRequestFormat();
    $format = $format ? $format : 'html';

    if (sfContext::getInstance()->getUser()->hasCredential('ViewAdminBar') && $format == 'html')
    {
      $this->loadAdminMenuAssets();

      $this->dispatcher->connect('response.filter_content', array($this, 'addEditorHtml'));
    }
  }

  public function loadAdminMenuAssets()
  {
    $response = sfContext::getInstance()->getResponse();
    $response->addStylesheet(sfSympalConfig::getAssetPath('/sfSympalAdminPlugin/css/menu.css'));
    $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalAdminPlugin/js/menu.js'));
  }

  public function addEditorHtml(sfEvent $event, $content)
  {
    $content = str_replace('</body>', get_sympal_admin_menu().'</body>', $content);
    return $content;
  }

  public function loadAdminMenu(sfEvent $event)
  {
    $menu = $event->getSubject();
    $manageContent = $menu->getChild('Content');

    $user = sfContext::getInstance()->getUser();

    $contentTypes = Doctrine_Core::getTable('sfSympalContentType')->getAllContentTypes();
    foreach ($contentTypes as $contentType)
    {
      $manageContent->addChild($contentType->getLabel(), '@sympal_content_list_type?type='.$contentType->getId());
    }

    $manageContent->addChild('Slots', '@sympal_content_slots');

    $siteAdministration = $menu->getChild('Site Administration');

    $siteAdministration
      ->addChild('404 Redirects', '@sympal_redirects')
      ->setCredentials(array('ManageContentSetup'));

    $siteAdministration
      ->addChild('Edit Site', '@sympal_sites_edit?id='.sfSympalContext::getInstance()->getSite()->getId())
      ->setCredentials(array('ManageContentSetup'));

    $administration = $menu->getChild('Administration');

    $administration->addChild('Content Types', '@sympal_content_types')
      ->setCredentials(array('ManageContentSetup'));

    $administration->addChild('Themes', '@sympal_themes')
      ->setCredentials(array('ManageThemes'));

    $administration->addChild('Sites', '@sympal_sites')
      ->setCredentials(array('ManageSites'));

    $administration->addChild('System Settings', '@sympal_config')
      ->setCredentials(array('ManageConfiguration'));
  }

  public function loadConfigForm(sfEvent $event)
  {
    $form = $event->getSubject();

    $array = sfSympalFormToolkit::getThemeWidgetAndValidator();
    $form->addSetting(null, 'rows_per_page', 'Rows Per Page');
    $form->addSetting(null, 'default_theme', 'Default Theme', $array['widget'], $array['validator']);
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
    $this->configuration->loadHelpers(array('Asset', 'Partial', 'I18N'));

    $menu = $event->getSubject();
    $content = $event['content'];
    $menuItem = $event['menuItem'];

    $sympalConfiguration = sfSympalConfiguration::getActive();
    $user = sfContext::getInstance()->getUser();
    $request = sfContext::getInstance()->getRequest();

    $contentEditor = $menu->getChild($content->getType()->getLabel() . ' Actions')
      ->setCredentials(array('ManageContent'));

    if ($sympalConfiguration->isAdminModule())
    {
      $contentEditor->addChild(image_tag('/sf/sf_admin/images/list.png').' '.__('View '.$content->getType()->getLabel()), $content->getRoute());    
    }

    $contentEditor->addChild(image_tag('/sf/sf_admin/images/add.png').' '.__('Create New '.$content->getType()->getLabel()), '@sympal_content_create_type?type='.$content['Type']['slug']);
    $contentEditor->addChild(image_tag('/sf/sf_admin/images/edit.png').' '.__('Edit '.$content->getType()->getLabel()), $content->getEditRoute());      
    $contentEditor->addChild(image_tag('/sf/sf_admin/images/edit.png').' '.__('Edit Content Type'), '@sympal_content_types_edit?id='.$content->getType()->getId());      

    if ($menuItem && $menuItem->exists())
    {
      $contentEditor->addChild(image_tag('/sf/sf_admin/images/edit.png').' '.__('Edit Menu Item'), '@sympal_content_menu_item?id='.$content->getId());
    } else {
      $contentEditor->addChild(image_tag('/sf/sf_admin/images/add.png').' '.__('Add to Menu'), '@sympal_content_menu_item?id='.$content->getId());
    }
  }
}