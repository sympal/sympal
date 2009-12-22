<?php

class sfSympalPluginConfiguration extends sfPluginConfiguration
{
  const VERSION = '0.7.0';

  public static
    $dependencies = array(
      'sfDoctrineGuardPlugin',
      'sfFormExtraPlugin',
      'sfTaskExtraPlugin',
      'sfFeed2Plugin',
      'sfWebBrowserPlugin',
      'sfJqueryReloadedPlugin',
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
      'sfSympalFrontendEditorPlugin'
    );

  public
    $sympalConfiguration;

  public static function enableSympalPlugins(ProjectConfiguration $configuration, $plugins = array())
  {
    if ($application = sfConfig::get('sf_app'))
    {
      $reflection = new ReflectionClass($application.'Configuration');
      if ($reflection->getConstant('disableSympal'))
      {
        return false;
      }
    }

    $configuration->enablePlugins('sfDoctrinePlugin');
    $configuration->enablePlugins('sfSympalPlugin');
    $configuration->enablePlugins(self::$dependencies);
    $configuration->enablePlugins($plugins);

    $sympalPluginPath = dirname(dirname(__FILE__));
    $configuration->setPluginPath('sfSympalPlugin', $sympalPluginPath);
    
    $embeddedPluginPath = $sympalPluginPath.'/lib/plugins';
    foreach (self::$dependencies as $plugin)
    {
      $configuration->setPluginPath($plugin, $embeddedPluginPath.'/'.$plugin);
    }
  }

  public function initialize()
  {
    $this->sympalConfiguration = new sfSympalConfiguration($this->dispatcher, $this->configuration);
    
    $this->dispatcher->connect('sympal.load_admin_menu', array($this, 'loadAdminMenu'));
    $this->dispatcher->connect('sympal.load_config_form', array($this, 'loadConfig'));
    $this->dispatcher->connect('sympal.load_editor', array($this, 'loadTools'));
    $this->dispatcher->connect('form.post_configure', array($this, 'formPostConfigure'));
  }

  public function getSympalConfiguration()
  {
    return $this->sympalConfiguration;
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
        $node = $menu->addChild('Manage '.$contentType->getLabel().' Content', '@sympal_content_list_type?type='.$contentType->getId());
        $node->addChild('Create', '@sympal_content_create_type?type='.$contentType->getId());
        $node->addChild('List', '@sympal_content_list_type?type='.$contentType->getId());
      }
    }

    $administration = $menu->getChild('Administration');

    $administration->addChild('Sites', '@sympal_sites')
      ->setCredentials(array('ManageSites'));

    $administration->addChild('Configuration', '@sympal_config')
      ->setCredentials(array('ManageConfiguration'));

    $content = $menu->addChild('Content Setup', sfContext::getInstance()->getRequest()->getUri())
      ->setCredentials(array('ManageContentSetup'));

    $content->addChild('Types', '@sympal_content_types');
    $content->addChild('Templates', '@sympal_content_templates');
    $content->addChild('Slot Types', '@sympal_content_slot_types');
  }

  public function loadConfig(sfEvent $event)
  {
    $form = $event->getSubject();

    $array = sfSympalFormToolkit::getLayoutWidgetAndValidator();
    $form->addSetting(null, 'default_layout', 'Default Layout', $array['widget'], $array['validator']);
    $form->addSetting(null, 'disallow_php_in_content', 'Disable PHP in Content', 'InputCheckbox', 'Boolean');
    $form->addSetting(null, 'rows_per_page', 'Rows Per Page');
    $form->addSetting(null, 'recaptcha_public_key', 'Recaptcha Public Key');
    $form->addSetting(null, 'recaptcha_private_key', 'Recaptcha Private Key');
    $form->addSetting(null, 'breadcrumbs_separator', 'Breadcrumbs Separator');
    $form->addSetting(null, 'config_form_class', 'Config Form Class');
    $form->addSetting(null, 'default_from_email_address', 'Default From Address');

    $form->addSetting('plugin_api', 'username', 'Username or API Key');
    $form->addSetting('plugin_api', 'password');
  }

  public function loadTools(sfEvent $event)
  {
    $menu = $event->getSubject();
    $content = $event['content'];
    $user = sfContext::getInstance()->getUser();
    $request = sfContext::getInstance()->getRequest();

    $contentEditor = $menu->addChild($content['Type']['label'] . ' Actions')
      ->setCredentials(array('ManageContent'));

    if ($request['module'] == 'sympal_content')
    {
      $contentEditor->addChild(image_tag('/sf/sf_admin/images/edit.png').' View '.$content['Type']['label'], $content->getRoute());
    } else {
      $contentEditor->addChild(image_tag('/sf/sf_admin/images/edit.png').' Edit '.$content['Type']['label'], $content->getEditRoute());      
    }

    if ($content->getTemplate() && $content->getTemplate()->getId())
    {
      $contentEditor->addChild(image_tag('/sf/sf_admin/images/edit.png').' Edit Content Template', '@sympal_content_templates_edit?id='.$content->getTemplate()->getId());      
    }

    $contentEditor->addChild(image_tag('/sf/sf_admin/images/edit.png').' Edit Content Type', '@sympal_content_types_edit?id='.$content->getType()->getId());      

    if ($content['is_published'])
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

  public function formPostConfigure(sfEvent $event)
  {
    $form = $event->getSubject();
    if ($form instanceof sfFormDoctrine)
    {
      sfSympalFormToolkit::embedI18n($form->getObject(), $form);

      if (sfSympalConfig::get('remove_timestampable_from_forms', null, true))
      {
        unset($form['created_at'], $form['updated_at']);
      }
    }
  }
}