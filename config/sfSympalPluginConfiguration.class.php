<?php

class sfSympalPluginConfiguration extends sfPluginConfiguration
{
  public static
    $dependencies = array(
      'sfDoctrineGuardPlugin',
      'sfFormExtraPlugin',
      'sfSympalForgotPasswordPlugin',
      'sfSympalCommentsPlugin',
      'sfSympalMenuPlugin',
      'sfSympalRegisterPlugin',
      'sfSympalSecurityPlugin',
      'sfSympalUserProfilePlugin',
      'sfSympalPluginManagerPlugin',
      'sfSympalPagesPlugin',
    );

  public
    $sympalConfiguration;

  public function initialize()
  {
    $this->sympalConfiguration = sfSympalConfiguration::getSympalConfiguration($this->dispatcher, $this->configuration);

    $this->dispatcher->connect('sympal.load_admin_bar', array($this, 'loadAdminBar'));
    $this->dispatcher->connect('sympal.load_settings_form', array($this, 'loadSettings'));
    $this->dispatcher->connect('context.load_factories', array($this, 'loadContext'));
  }

  public function loadContext()
  {
    sfSympalContext::createInstance(sfConfig::get('sf_app'), sfContext::getInstance());
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
  }

  public function loadAdminBar(sfEvent $event)
  {
    $menu = $event['menu'];

    $user = sfContext::getInstance()->getUser();
    $mode = $user->getAttribute('sympal_edit') ? 'off':'on';
    $currentMode = $user->getAttribute('sympal_edit') ? 'on':'off';
    $menu->addChild(image_tag('/sf/sf_admin/images/edit.png').' Turn '.ucfirst($mode), '@sympal_toggle_edit', array('title' => 'Click to turn '.$mode.' edit mode. Edit mode is currently '.$currentMode.'.', 'class' => $mode));

    $icon = $menu->getChild('Icon');
    $icon->addChild('Go To Homepage', '@sympal_homepage');
    $icon->addChild('Logout', '@sympal_logout', 'confirm=Are you sure you wish to logout?');

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

    if (sfSympalTools::isEditMode())
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
    $administration->addChild('Menus', '@sympal_menu_items');
    $administration->addChild('Content Types', '@sympal_content_types');
    $administration->addChild('Content Templates', '@sympal_content_templates');
    $administration->addChild('Content Slot Types', '@sympal_content_slot_types');
    $administration->addChild('Configuration', '@sympal_config');
  }
}