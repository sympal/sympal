<?php

class sfSympalPluginConfiguration extends sfPluginConfiguration
{
  public static
    $dependencies = array(
      'sfFormExtraPlugin',
      'sfTaskExtraPlugin',
      'sfSympalUserPlugin',
      'sfSympalMenuPlugin',
      'sfSympalPluginManagerPlugin',
      'sfSympalPagesPlugin',
    );

  public
    $sympalConfiguration;

  public static function enableSympalPlugins(ProjectConfiguration $configuration)
  {
    $sympalPluginPath = dirname(dirname(__FILE__));
    $configuration->setPluginPath('sfSympalPlugin', $sympalPluginPath);
    $dependencies = self::$dependencies;
    $embeddedPluginPath = $sympalPluginPath.'/lib/plugins';
    foreach ($dependencies as $plugin)
    {
      $configuration->setPluginPath($plugin, $embeddedPluginPath.'/'.$plugin);
    }
  }

  public function initialize()
  {
    $this->sympalConfiguration = sfSympalConfiguration::getSympalConfiguration($this->dispatcher, $this->configuration);

    $this->dispatcher->connect('sympal.load_admin_bar', array($this, 'loadAdminBar'));
    $this->dispatcher->connect('sympal.load_settings_form', array($this, 'loadSettings'));
    $this->dispatcher->connect('sympal.load_tools', array($this, 'loadTools'));
    $this->dispatcher->connect('context.load_factories', array($this, 'loadContext'));
  }

  public function loadContext()
  {
    sfSympalContext::createInstance(sfConfig::get('sf_app'), sfContext::getInstance());
  }

  public function loadTools(sfEvent $event)
  {
    $menu = $event['menu'];
    $content = $event['content'];
    $lock = $event['lock'];
    $user = sfContext::getInstance()->getUser();
    $request = sfContext::getInstance()->getRequest();

    $contentEditor = $menu->addChild($content['Type']['name'] . ' Editor');

    if ($content['locked_by'])
    {
       if ($content['locked_by'] == $user->getGuardUser()->getId())
       {
         if ($request->getParameter('module') == 'sympal_content')
         {
           $contentEditor->addChild(image_tag('/sf/sf_admin/images/edit.png').' Edit '.$content['Type']['name'].' Inline', $content->getRoute());
         } else {
           $contentEditor->addChild(image_tag('/sf/sf_admin/images/edit.png').' Edit '.$content['Type']['name'].' Backend', '@sympal_content_edit?id='.$content['id']);
         }
       } else {
         $contentEditor->addChild($content['Type']['name'].' is currently locked by "'.$content['LockedBy']['username'].'" and cannot be edited.');
       }
    }

    if ($content['is_published'])
    {
      $contentEditor->addChild(image_tag('/sf/sf_admin/images/cancel.png').' Un-Publish', '@sympal_unpublish_content?id='.$content['id']);
    } else {
      $contentEditor->addChild(image_tag('/sf/sf_admin/images/tick.png').' Publish', '@sympal_publish_content?id='.$content['id']);
    }
    $contentEditor->addChild(image_tag('/sf/sf_admin/images/delete.png').' Delete', '@sympal_content_delete?id='.$content['id']);

    $contentType = $menu->addChild($content['Type']['name'].' Content');
    $contentType->addChild(image_tag('/sf/sf_admin/images/add.png').' Create', '@sympal_content_create_type?type='.$content['Type']['name']);
    $contentType->addChild(image_tag('/sf/sf_admin/images/list.png').' List', '@sympal_content_type_'.$content['Type']['slug']);

    if (sfSympalConfig::isI18nEnabled())
    {
      $menu->addChild('Change Language')
        ->addChild(get_component('sympal_editor', 'language'));
    }
  }

  public function getSympalConfiguration()
  {
    return $this->sympalConfiguration;
  }

  public function loadSettings(sfEvent $event)
  {
    $form = $event->getSubject();

    $form->addSetting(null, 'disallow_php_in_content', 'Disable PHP in Content', 'InputCheckbox', 'Boolean');
    $form->addSetting(null, 'rows_per_page', 'Rows Per Page');
    $form->addSetting(null, 'recaptcha_public_key', 'Recaptcha Public Key');
    $form->addSetting(null, 'recaptcha_private_key', 'Recaptcha Private Key');
  }

  public function loadAdminBar(sfEvent $event)
  {
    $menu = $event['menu'];

    $user = sfContext::getInstance()->getUser();

    $icon = $menu->getChild('Icon');
    $icon->addChild('Go To Homepage', '@sympal_homepage');
    $icon->addChild('Signout', '@sympal_signout', 'confirm=Are you sure you wish to signout?');

    $help = $icon->addChild('Help');
    $help->addChild('Logged in as '.$user->getGuardUser()->getUsername());
    $help->addChild('Sympal '.sfSympal::VERSION);
    $help->addChild('symfony '.SYMFONY_VERSION);
    $help->addChild('Doctrine '.Doctrine::VERSION);
    $help->addChild('About Sympal', 'http://www.symfony-project.com/plugins/sfSympalPlugin', 'target=_BLANK');
    $help->addChild('About Symfony', 'http://www.symfony-project.com/about', 'target=_BLANK');
    $help->addChild('Documentation', 'http://www.symfony-project.com/plugins/sfSympalPlugin', 'target=_BLANK');
    $help->addChild('Doctrine Documentation', 'http://www.doctrine-project.org/documentation', 'target=_BLANK');
    $help->addChild('symfony Documentation', 'http://www.symfony-project.org/doc', 'target=_BLANK');
    $help->addChild('Report Doctrine Bug', 'http://trac.doctrine-project.org', 'target=_BLANK');
    $help->addChild('Report symfony Bug', 'http://trac.symfony-project.com', 'target=_BLANK');

    if (sfSympalToolkit::isEditMode())
    {
      $content = $menu->addChild('Content', '@sympal_content');
      $contentTypes = Doctrine::getTable('ContentType')->findAll();
      $content->addChild('Create New Content', '@sympal_content_new');
      foreach ($contentTypes as $contentType)
      {
        $node = $content->addChild($contentType->getLabel());
        $node->addChild('Create', '@sympal_content_create_type?type='.$contentType->getSlug());
        $node->addChild('List', '@sympal_content');
      }
    }

    $administration = $menu->getChild('Administration');
    $administration->addChild('Sites', '@sympal_sites');
    $administration->addChild('Configuration', '@sympal_config');

    $content = $administration->addChild('Content');
    $content->addChild('Types', '@sympal_content_types');
    $content->addChild('Templates', '@sympal_content_templates');
    $content->addChild('Slot Types', '@sympal_content_slot_types');
  }
}