<?php

/**
 * Generates a new sympal plugin of a variety of types
 * 
 * @package     sfSympalPluginManager
 * @subpackage  task
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */
class sfSympalPluginGenerateTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'The name of the functionality. i.e. sfSympal#NAME#Plugin'),
    ));

    $this->addOptions(array(
      new sfCommandOption('module', null, sfCommandOption::PARAMETER_REQUIRED | sfCommandOption::IS_ARRAY, 'Add a module'),
      new sfCommandOption('test-application', null, sfCommandOption::PARAMETER_REQUIRED, 'A name for the initial test application', 'frontend'),
      new sfCommandOption('skip-test-dir', null, sfCommandOption::PARAMETER_NONE, 'Skip generation of the plugin test directory'),
      new sfCommandOption('content-type', null, sfCommandOption::PARAMETER_OPTIONAL, 'The name of the content type to create', null),
      new sfCommandOption('theme', null, sfCommandOption::PARAMETER_OPTIONAL, 'The name of the skeleton theme to generate in the plugin'),
      new sfCommandOption('re-generate', null, sfCommandOption::PARAMETER_NONE, 'Re-generate the plugin. Will remove it if it exists already and re-generate everything.'),
      new sfCommandOption('install', null, sfCommandOption::PARAMETER_NONE, 'Install the plugin after generating it.'),
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
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

    if (!$options['no-confirmation'] && !$this->askConfirmation(array('This command will create a new plugin named '.$pluginName, 'Are you sure you want to proceed? (y/N)'), 'QUESTION_LARGE', false))
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
        $uninstallOptions[] = '--delete';
        $uninstallOptions[] = '--no-confirmation';
        $ret = $uninstall->run(array($name), $uninstallOptions);
      } else {
        throw new sfException('A plugin with the name '.$pluginName.' already exists!');
      }
    }

    if (is_dir($path))
    {
      Doctrine_Lib::removeDirectories($path);
    }

    $generatePlugin = new sfGeneratePluginTask($this->dispatcher, $this->formatter);
    $generatePlugin->setCommandApplication($this->commandApplication);

    $generatePluginOptions = array();
    if (isset($options['module']) && !empty($options['module']))
    {
      $generatePluginOptions[] = '--module='.implode(' --module=', $options['module']);
    }
    if (isset($options['test-application']))
    {
      $generatePluginOptions[] = '--test-application='.$options['test-application'];
    }
    if (isset($options['skip-test-dir']))
    {
      $generatePluginOptions[] = '--skip-test-dir';
    }
    $generatePlugin->run(array($pluginName), $generatePluginOptions);

    $contentType = isset($options['content-type']) ? $options['content-type']:null;
    $slug = str_replace('-', '_', Doctrine_Inflector::urlize($name));
    $lowerName = str_replace('-', '_', $slug);

    if ($contentType)
    {
      $pluginYamlSchema = <<<EOF
---
$contentType:
  actAs: [sfSympalContentTypeTemplate]
  columns:
    title: string(255)
    body: clob
EOF;

      $pluginInstallDataFixtures = <<<EOF
# $pluginName install data fixtures
sfSympalContentType:
  content_type_$lowerName:
    name: $contentType
    slug: $slug
    description: $lowerName content type
    label: $lowerName content type
    default_path: /$lowerName/:slug
EOF;
    }

    $itemsToCreate = array(
      'config' => null,
      'config/doctrine' => null,
      'config/routing.yml' => '# '.$pluginName.' routing',
      'data' => null
    );

    if (isset($pluginInstallDataFixtures))
    {
      $itemsToCreate['data/fixtures'] = null;
      $itemsToCreate['data/fixtures/'.$contentType.'.yml'] = $pluginInstallDataFixtures;
    }

    if (isset($pluginYamlSchema))
    {
      $itemsToCreate['config/doctrine/schema.yml'] = $pluginYamlSchema;
    }
    
    $appYaml = '';

    if (isset($options['theme']))
    {
      $appYaml .= sprintf('all:
  theme:
    themes:
      %s:
        layout: %s
        stylesheets:
          - %s', $options['theme'], $options['theme'], '/'.$pluginName.'/css/'.$options['theme'].'.css');

      $itemsToCreate['templates/'.$options['theme'].'.php'] = file_get_contents($this->configuration->getPluginConfiguration('sfSympalPlugin')->getRootDir().'/templates/default.php');
      $itemsToCreate['web/css/'.$options['theme'].'.css'] = file_get_contents($this->configuration->getPluginConfiguration('sfSympalPlugin')->getRootDir().'/web/themes/default/css/main.css');
    }

    if ($contentType)
    {
      $appYaml .= sprintf('all:
  sympal_config:
    content_types:
      %s:
        content_templates:
          default_view:
            template:     %s/view', $contentType, $contentType);
      
      $itemsToCreate['modules'] = null;
      $itemsToCreate['modules/'.$contentType] = null;
      $itemsToCreate['modules/'.$contentType.'/templates'] = null;
      $itemsToCreate['modules/'.$contentType.'/templates/_view.php'] = "<?php echo get_sympal_content_slot(\$content, 'title') ?>";
    }

    if ($appYaml)
    {
      $itemsToCreate['config/app.yml'] = $appYaml;
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

    $pluginAssets = new sfPluginPublishAssetsTask($this->dispatcher, $this->formatter);
    $pluginAssets->run(array(), array());

    $cc = new sfCacheClearTask($this->dispatcher, $this->formatter);
    $ret = $cc->run(array(), array());
  }
}