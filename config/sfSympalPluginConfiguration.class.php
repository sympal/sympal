<?php

class sfSympalPluginConfiguration extends sfPluginConfiguration
{
  public 
    $dependencies = array(
      'sfDoctrineGuardPlugin',
      'sfFormExtraPlugin',
      'sfSympalCommentsPlugin',
      'sfSympalMenuPlugin',
      'sfSympalPagesPlugin',
      'sfSympalRegisterPlugin',
      'sfSympalSecurityPlugin',
      'sfSympalForgotPasswordPlugin',
      'sfSympalUserProfilePlugin',
      'sfSympalForgotPasswordPlugin',
      'sfSympalPluginManagerPlugin',
    ),
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
    $menu->addNode(image_tag('/sf/sf_admin/images/edit.png').' Turn '.ucfirst($mode), '@sympal_toggle_edit', array('title' => 'Click to turn '.$mode.' edit mode. Edit mode is currently '.$currentMode.'.', 'class' => $mode));

    $icon = $menu->getNode('Icon');
    $icon->addNode('Go To Homepage', '@sympal_homepage');
    $icon->addNode('Logout', '@sympal_logout', 'confirm=Are you sure you wish to logout?');

    $help = $icon->addNode('Help');
    $help->addNode('Logged in as '.$user->getGuardUser()->getUsername());
    $help->addNode('Sympal '.sfSympal::VERSION);
    $help->addNode('symfony '.SYMFONY_VERSION);
    $help->addNode('Doctrine '.Doctrine::VERSION);
    $help->addNode('About Sympal', 'http://www.symfony-project.com/plugins/sfSympalPlugin', 'target=_BLANK');
    $help->addNode('About Symfony', 'http://www.symfony-project.com/about', 'target=_BLANK');
    $help->addNode('Documentation', 'http://www.symfony-project.com/plugins/sfSympalPlugin', 'target=_BLANK');
    $help->addNode('Doctrine Documentation', 'http://www.doctrine-project.org/documentation', 'target=_BLANK');
    $help->addNode('symfony Documentation', 'http://www.symfony-project.org/doc', 'target=_BLANK');
    $help->addNode('Report Doctrine Bug', 'http://trac.doctrine-project.org', 'target=_BLANK');
    $help->addNode('Report symfony Bug', 'http://trac.symfony-project.com', 'target=_BLANK');

    if (sfSympalTools::isEditMode())
    {
      $entities = $menu->addNode('Content', '@sympal_entities');
      $entityTypes = Doctrine::getTable('EntityType')->findAll();
      $entities->addNode('Create New Content', '@sympal_entities_new');
      foreach ($entityTypes as $entityType)
      {
        $node = $entities->addNode($entityType->getLabel());
        $node->addNode('Create', '@sympal_entities_create_type?type='.$entityType->getSlug());
        $node->addNode('List', '@sympal_entities');
      }
    }

    $administration = $menu->getNode('Administration');
    $administration->addNode('Sites', '@sympal_sites');
    $administration->addNode('Menus', '@sympal_menu_items');
    $administration->addNode('Entity Types', '@sympal_entity_types');
    $administration->addNode('Entity Templates', '@sympal_entity_templates');
    $administration->addNode('Entity Slot Types', '@sympal_entity_slot_types');
    $administration->addNode('Configuration', '@sympal_config');
  }
}