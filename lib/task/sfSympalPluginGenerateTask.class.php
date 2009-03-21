<?php

class sfSympalPluginGenerateTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'The name of the functionality. i.e. sfSympal#NAME#Plugin'),
    ));

    $this->addOptions(array(
      new sfCommandOption('entity-type', null, sfCommandOption::PARAMETER_OPTIONAL, 'The name of the entity type to create', null),
      new sfCommandOption('re-generate', null, sfCommandOption::PARAMETER_NONE, 'Re-generate the plugin. Will remove it if it exists already and re-generate everything.'),
      new sfCommandOption('install', null, sfCommandOption::PARAMETER_NONE, 'Install the plugin after generating it.'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'plugin-generate';
    $this->briefDescription = 'Generate the skeleton for a sympal plugin';

    $this->detailedDescription = <<<EOF
The [sympal:plugin-create|INFO] is a task to help you with generating a skeleton sympal plugin.
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $name = $arguments['name'];
    $pluginName = 'sfSympal'.Doctrine_Inflector::classify($name).'Plugin';
    $path = sfConfig::get('sf_plugins_dir').'/'.$pluginName;

    if (!$this->askConfirmation(array('This command will create a new plugin named '.$pluginName, 'Are you sure you want to proceed? (y/N)'), null, false))
    {
      $this->logSection('sympal', 'Plugin creation aborted');

      return 1;
    }

    if (is_dir($path))
    {
      if (isset($options['re-generate']))
      {
        $uninstall = new sfSympalPluginUninstallTask($this->dispatcher, $this->formatter);
        $uninstall->setCommandApplication($this->commandApplication);
        $ret = $uninstall->run(array($name), array());
      } else {
        throw new sfException('A plugin with the name '.$pluginName.' already exists!');
      }
    }

    mkdir($path);

    $entityType = isset($options['entity-type']) ? $options['entity-type']:$name;
    $pluginYamlSchema = <<<EOF
---
$entityType:
  actAs: [sfSympalEntityType]
  columns:
    name: string(255)
    body: clob
EOF;

    $pluginConfigurationClassCode = <<<EOF
<?php
class %s extends sfPluginConfiguration
{
  public 
    \$dependencies = array(
      'sfSympalPlugin'
    );

  public function initialize()
  {
    \$this->dispatcher->connect('sympal.load_settings_form', array(\$this, 'loadSettings'));
    \$this->dispatcher->connect('sympal.load_admin_bar', array(\$this, 'loadAdminBar'));
  }

  public function loadAdminBar(sfEvent \$event)
  {
    \$menu = \$event['menu'];

    \$menu->getNode('Administration')->addNode('$name', '@sympal_entity_type_%s');
  }

  public function loadSettings(sfEvent \$event)
  {
    \$form = \$event->getSubject();

    // \$form->addSetting('$name', 'setting_name', 'Setting Label', 'InputCheckbox', 'Boolean');
  }
}
EOF;

    $lowerName = str_replace('-', '_', Doctrine_Inflector::urlize($name));
    $pluginConfigurationClassCode = sprintf($pluginConfigurationClassCode, $pluginName.'Configuration', $lowerName);

    $pluginInstallDataFixtures = <<<EOF
# $pluginName install data fixtures

EntityType:
  EntityType_$lowerName:
    name: $entityType
    label: $entityType
    slug: $lowerName
    list_route_url: /$lowerName/list
    view_route_url: /$lowerName/:slug

Entity:
  {$entityType}_entity_sample:
    Type: EntityType_$lowerName
    slug: sample-$lowerName
    Site: Site_default
    is_published: true
    CreatedBy: admin

EntityTemplate:
  EntityTemplate_View$entityType:
    name: View $entityType
    type: View
    EntityType: EntityType_$lowerName
    body: |
      <h1>[?php echo \$entity->getHeaderTitle() ?]</h1><p>[?php echo \$entity->getRecord()->getBody() ?]</p>

$entityType:
  {$entityType}_sample:
    name: Sample $entityType
    body: This is some sample content for the body your new entity type.
    Entity: {$entityType}_entity_sample

MenuItem:
  MenuItem_primary:
    children:
      Menuitem_primary_$lowerName:
        name: $name
        is_published: true
        label: $name
        has_many_entities: true
        Site: Site_default
        EntityType: EntityType_$lowerName
EOF;

    $itemsToCreate = array(
      'config' => null,
      'config/doctrine' => null,
      'config/routing.yml' => '# '.$pluginName.' routing',
      'config/'.$pluginName.'Configuration.class.php' => $pluginConfigurationClassCode,
      'data' => null,
      'data/fixtures' => null,
      'data/fixtures/install.yml' => $pluginInstallDataFixtures,
    );

    if (isset($pluginYamlSchema))
    {
      $itemsToCreate['config/doctrine/schema.yml'] = $pluginYamlSchema;
    }

    foreach ($itemsToCreate as $item => $value)
    {
      $itemPath = $path.'/'.$item;
      if (!is_null($value))
      {
        $dir = dirname($itemPath);

        $this->getFilesystem()->mkdirs($dir);
        file_put_contents($itemPath, $value);
      } else {
        $this->getFilesystem()->mkdirs($itemPath);
      }
    }

    if (isset($options['install']) && $options['install'])
    {
      $install = new sfSympalPluginInstallTask($this->dispatcher, $this->formatter);
      $install->setCommandApplication($this->commandApplication);
      $ret = $install->run(array($name), array());
    }
  }
}