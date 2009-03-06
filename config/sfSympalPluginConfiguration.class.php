<?php

class sfSympalPluginConfiguration extends sfPluginConfiguration
{
  public 
    $dependencies = array(
      'sfDoctrineGuardPlugin',
      'sfFormExtraPlugin',
      'sfSympalCommentsPlugin',
      'sfSympalI18nPlugin',
      'sfSympalMenuPlugin',
      'sfSympalPagesPlugin',
      'sfSympalRegisterPlugin',
      'sfSympalSecurityPlugin'
    ),
    $sympalConfiguration;

  public function initialize()
  {
    $this->sympalConfiguration = sfSympalConfiguration::getSympalConfiguration($this->dispatcher, $this->configuration);

    $this->dispatcher->connect('sympal.load_admin_bar', array($this, 'loadAdminBar'));
    $this->dispatcher->connect('sympal.load_settings_form', array($this, 'loadSettings'));
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

    $icon = $menu->getNode('Icon');

    $user = sfContext::getInstance()->getUser();
    $mode = $user->getAttribute('sympal_edit') ? 'Off':'On';
    $icon->addNode('Turn Edit Mode '.$mode, '@sympal_toggle_edit');
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

    $entities = $menu->addNode('Entities', '@sympal_entities');
    $entityTypes = Doctrine::getTable('EntityType')->findAll();
    foreach ($entityTypes as $entityType)
    {
      $node = $entities->addNode($entityType->getLabel());
      $node->addNode('Create', '@sympal_entities_create_type?type='.$entityType->getName());
      $node->addNode('List', '@sympal_entities');
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