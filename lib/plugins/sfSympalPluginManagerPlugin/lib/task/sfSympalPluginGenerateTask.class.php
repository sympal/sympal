<?php

class sfSympalPluginGenerateTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'The name of the functionality. i.e. sfSympal#NAME#Plugin'),
    ));

    $this->addOptions(array(
      new sfCommandOption('content-type', null, sfCommandOption::PARAMETER_OPTIONAL, 'The name of the content type to create', null),
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
    $pluginName = 'sfSympal'.Doctrine_Inflector::classify(Doctrine_Inflector::tableize($name)).'Plugin';
    $path = sfConfig::get('sf_plugins_dir').'/'.$pluginName;

    if (!$this->askConfirmation(array('This command will create a new plugin named '.$pluginName, 'Are you sure you want to proceed? (y/N)'), null, false))
    {
      $this->logSection('sympal', 'Plugin creation aborted');

      return 1;
    }

    if (is_dir($path))
    {
      if (isset($options['re-generate']) && $options['re-generate'])
      {
        $uninstall = new sfSympalPluginUninstallTask($this->dispatcher, $this->formatter);
        $uninstall->setCommandApplication($this->commandApplication);
        $uninstallOptions = array();
        if (isset($options['content-type']))
        {
          $uninstallOptions[] = '--content-type='.$options['content-type'];
        }
        $ret = $uninstall->run(array($name), $uninstallOptions);
      } else {
        throw new sfException('A plugin with the name '.$pluginName.' already exists!');
      }
    }

    if (is_dir($path))
    {
      Doctrine_Lib::removeDirectories($path);
    }

    mkdir($path);

    $contentType = isset($options['content-type']) ? $options['content-type']:null;
    $lowerName = str_replace('-', '_', Doctrine_Inflector::urlize($name));


      $pluginConfigurationClassCode = <<<EOF
<?php
class %s extends sfPluginConfiguration
{
  public static
    \$dependencies = array(
      'sfSympalPlugin'
    );
}
EOF;

    $pluginConfigurationClassCode = sprintf($pluginConfigurationClassCode, $pluginName.'Configuration', $lowerName);

    if ($contentType)
    {
      $pluginYamlSchema = <<<EOF
---
$contentType:
  actAs: [sfSympalContentType]
  columns:
    name: string(255)
    body: clob
EOF;

      $pluginInstallDataFixtures = <<<EOF
# $pluginName install data fixtures

ContentType:
  ContentType_$lowerName:
    name: $contentType
    label: $contentType
    slug: $lowerName
    list_path: /$lowerName/list
    view_path: /$lowerName/:slug

Content:
  {$contentType}_content_sample:
    Type: ContentType_$lowerName
    slug: sample-$lowerName
    Site: Site_default
    is_published: true
    CreatedBy: admin

ContentTemplate:
  ContentTemplate_View$contentType:
    name: View $contentType
    type: View
    ContentType: ContentType_$lowerName
    body: |
      [?php echo get_sympal_breadcrumbs(\$menuItem, \$content) ?]
      <h2>[?php echo \$content->getHeaderTitle() ?]</h2>
      <p><strong>Posted by [?php echo \$content->CreatedBy->username ?] on [?php echo date('m/d/Y h:i:s', strtotime(\$content->created_at)) ?]</strong></p>
      <p>[?php echo \$content->getRecord()->getBody() ?]</p>
      [?php echo get_sympal_comments(\$content) ?]

$contentType:
  {$contentType}_sample:
    name: Sample $contentType
    body: This is some sample content for the body your new content type.
    Content: {$contentType}_content_sample

MenuItem:
  MenuItem_primary:
    children:
      Menuitem_primary_$lowerName:
        name: $name
        is_published: true
        label: $name
        is_content_type_list: true
        Site: Site_default
        ContentType: ContentType_$lowerName
EOF;

    }

    $itemsToCreate = array(
      'config' => null,
      'config/doctrine' => null,
      'config/routing.yml' => '# '.$pluginName.' routing',
      'config/'.$pluginName.'Configuration.class.php' => $pluginConfigurationClassCode,
      'data' => null
    );

    if (isset($pluginInstallDataFixtures))
    {
      $itemsToCreate['data/fixtures'] = null;
      $itemsToCreate['data/fixtures/install.yml'] = $pluginInstallDataFixtures;
    }

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
      $installOptions = array();
      if (isset($options['content-type']))
      {
        $installOptions[] = '--content-type='.$options['content-type'];
      }
      $ret = $install->run(array($name), $installOptions);
    }
  }
}