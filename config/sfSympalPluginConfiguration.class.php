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
    );

  public function initialize()
  {
    sfConfig::set('sf_enabled_modules', array_merge(sfConfig::get('sf_enabled_modules', array()), sfSympalTools::getModules()));

    sfConfig::set('sf_admin_module_web_dir', '/sfSympalPlugin');

    sfConfig::set('sf_login_module', 'sympal_auth');
    sfConfig::set('sf_login_action', 'login');

    sfConfig::set('sf_secure_module', 'sympal_frontend');
    sfConfig::set('sf_secure_action', 'secure');

    sfConfig::set('sf_error_404_module', 'sympal_frontend');
    sfConfig::set('sf_error_404_action', 'error404');

    sfConfig::set('sf_module_disabled_module', 'sympal_frontend');
    sfConfig::set('sf_module_disabled_action', 'disabled');

    $options = array('baseClassName' => 'sfSympalDoctrineRecord');
    $options = array_merge(sfConfig::get('doctrine_model_builder_options', array()), $options);
    sfConfig::set('doctrine_model_builder_options', $options);

    $this->dispatcher->connect('sympal.load_admin_bar', array($this, 'loadAdminBar'));
    $this->dispatcher->connect('sympal.load_settings_form', array($this, 'loadSettings'));
    $this->dispatcher->connect('context.load_factories', array($this, 'bootstrap'));
  }

  public function bootstrap()
  {
    $this->configuration->loadHelpers(array('Cmf'));
    
    $this->loadDoctrineCache();

    if (sfConfig::get('sf_debug'))
    {
      $this->checkPluginDependencies();
    }

    $response = sfContext::getInstance()->getResponse();
    $response->setTitle('Sympal');
  }

  public function loadDoctrineCache()
  {
    $manager = Doctrine_Manager::getInstance();
    $manager->setAttribute('auto_accessor_override', true);

    if (sfSympalConfig::get('enable_query_caching') || sfSympalConfig::get('enable_result_caching'))
    {
      $manager->setAttribute('use_dql_callbacks', true);
      $driver = new sfSympalDoctrineCacheDriver();
    }

    if (sfSympalConfig::get('enable_query_caching'))
    {
      $manager->setAttribute(Doctrine::ATTR_RESULT_CACHE, $driver);
    }

    if (sfSympalConfig::get('enable_result_caching'))
    {
      $manager->setAttribute(Doctrine::ATTR_QUERY_CACHE, $driver);
    }
  }

  public function checkPluginDependencies()
  {
    foreach ($this->configuration->getPlugins() as $pluginName)
    {
      if (strpos($pluginName, 'sfSympal') !== false)
      {
        $pluginConfiguration = $this->configuration->getPluginConfiguration($pluginName);
        if (isset($pluginConfiguration->dependencies) && !empty($pluginConfiguration->dependencies))
        {
          sfSympalTools::checkPluginDependencies($pluginConfiguration, $pluginConfiguration->dependencies);
        } else {
          throw new sfException(
            sprintf(
              'You must specify dependencies for %s',
              $pluginName
            )
          );
        }
      }
    }
  }

  public function loadSettings(sfEvent $event)
  {
    $form = $event->getSubject();

    $form->addSetting(null, 'disallow_php_in_content', 'Disable PHP in Content', 'InputCheckbox', 'Boolean');
    $form->addSetting(null, 'enable_result_caching', 'Enable Result Caching', 'InputCheckbox', 'Boolean');
    $form->addSetting(null, 'enable_query_caching', 'Enable Query Caching', 'InputCheckbox', 'Boolean');

/*
    $plugins = sfSympalTools::getPlugins();
    unset($plugins['sfSympalPlugin']);

    $pluginNames = array_combine(array_keys($plugins), array_keys($plugins));

    $widget = new sfWidgetFormChoice(array('expanded' => true, 'multiple' => true, 'choices' => $pluginNames));
    $validator = new sfValidatorChoice(array('required' => false, 'multiple' => true, 'choices' => $pluginNames));

    $form->addSetting(null, 'enabled_plugins', 'Enabled Plugins', $widget, $validator);
*/
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