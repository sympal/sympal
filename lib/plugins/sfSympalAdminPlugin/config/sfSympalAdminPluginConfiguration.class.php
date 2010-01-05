<?php

class sfSympalAdminPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    $this->dispatcher->connect('sympal.load_admin_menu', array($this, 'loadAdminMenu'));
    $this->dispatcher->connect('sympal.load_config_form', array($this, 'loadConfigForm'));
    $this->dispatcher->connect('sympal.load_editor', array($this, 'loadEditor'));
  }

  public function loadAdminMenu(sfEvent $event)
  {
    $menu = $event->getSubject();

    $user = sfContext::getInstance()->getUser();

    if ($user->isEditMode())
    {
      $contentTypes = Doctrine_Core::getTable('sfSympalContentType')->findAll();
      foreach ($contentTypes as $contentType)
      {
        $node = $menu->addChild('Manage '.$contentType->getLabel().' Content');
        $node->addChild('Create', '@sympal_content_create_type?type='.$contentType->getId());
        $node->addChild('List', '@sympal_content_list_type?type='.$contentType->getId());
      }
    }

    $administration = $menu->getChild('Administration');

    $administration->addChild('Sites', '@sympal_sites')
      ->setCredentials(array('ManageSites'));

    $administration->addChild('Content Types', '@sympal_content_types')
      ->setCredentials(array('ManageContentSetup'));

    $administration->addChild('Configuration', '@sympal_config')
      ->setCredentials(array('ManageConfiguration'));
  }

  public function loadConfigForm(sfEvent $event)
  {
    $form = $event->getSubject();

    $array = sfSympalFormToolkit::getThemeWidgetAndValidator();
    $form->addSetting(null, 'default_theme', 'Default Theme', $array['widget'], $array['validator']);
    $form->addSetting(null, 'rows_per_page', 'Rows Per Page');
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
    $menu = $event->getSubject();
    $content = $event['content'];
    $user = sfContext::getInstance()->getUser();
    $request = sfContext::getInstance()->getRequest();

    $contentEditor = $menu->addChild($content['Type']['label'] . ' Actions')
      ->setCredentials(array('ManageContent'));

    if ($request->getParameter('module') == 'sympal_content' || $request->getParameter('module') == 'sympal_menu_items')
    {
      $contentEditor->addChild(image_tag('/sf/sf_admin/images/list.png').' View '.$content['Type']['label'], $content->getRoute());
    }
    
    if ($request->getParameter('module') != 'sympal_content')
    {
      $contentEditor->addChild(image_tag('/sf/sf_admin/images/edit.png').' Edit '.$content['Type']['label'], $content->getEditRoute());      
    }

    $contentEditor->addChild(image_tag('/sf/sf_admin/images/edit.png').' Edit Content Type', '@sympal_content_types_edit?id='.$content->getType()->getId());      

    if ($content['date_published'])
    {
      $contentEditor->addChild(image_tag('/sf/sf_admin/images/cancel.png').' Un-Publish', '@sympal_unpublish_content?id='.$content['id']);
    } else {
      $contentEditor->addChild(image_tag('/sf/sf_admin/images/tick.png').' Publish', '@sympal_publish_content?id='.$content['id']);
    }

    $contentEditor->addChild(image_tag('/sf/sf_admin/images/add.png').' Create New '.$content['Type']['label'], '@sympal_content_create_type?type='.$content['Type']['slug']);

    if (sfSympalConfig::isI18nEnabled())
    {
      $menu->addChild('Change Language')
        ->addChild(get_component('sympal_default', 'language'));
    }
  }
}