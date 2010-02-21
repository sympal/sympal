<?php

require_once dirname(__FILE__).'/sfTaskExtraGeneratorBaseTask.class.php';

/**
 * Generates a new plugin.
 * 
 * @package     sfTaskExtraPlugin
 * @subpackage  task
 * @author      Kris Wallsmith <kris.wallsmith@symfony-project.com>
 * @version     SVN: $Id: sfGeneratePluginTask.class.php 25032 2009-12-07 17:17:38Z Kris.Wallsmith $
 */
class sfGeneratePluginTask extends sfTaskExtraGeneratorBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('plugin', sfCommandArgument::REQUIRED, 'The plugin name'),
    ));

    $this->addOptions(array(
      new sfCommandOption('module', null, sfCommandOption::PARAMETER_REQUIRED | sfCommandOption::IS_ARRAY, 'Add a module'),
      new sfCommandOption('test-application', null, sfCommandOption::PARAMETER_REQUIRED, 'A name for the initial test application', 'frontend'),
      new sfCommandOption('skip-test-dir', null, sfCommandOption::PARAMETER_NONE, 'Skip generation of the plugin test directory'),
    ));

    $this->namespace = 'generate';
    $this->name = 'plugin';

    $this->briefDescription = 'Generates a new plugin';

    $this->detailedDescription = <<<EOF
The [generate:plugin|INFO] task creates the basic directory structure for a
new plugin in the current project:

  [./symfony generate:plugin sfExamplePlugin|INFO]

You can customize the default skeleton used by the task by creating a
[%SF_DATA_DIR%/skeleton/plugin|COMMENT] directory.

You can also specify one or more modules you would like included in this
plugin using the [--module|COMMENT] option:

  [./symfony generate:plugin sfExamplePlugin --module=sfExampleFoo --module=sfExampleBar|INFO]

This task automatically generates all the necessary files for writing unit and
functional tests for your plugin, including an embedded symfony project and
application in [/test/fixtures/project|COMMENT]. You can customized the name
used with the [--test-application|COMMENT] option:

  [./symfony generate:plugin sfExamplePlugin --test-application=backend|INFO]

Use the [--skip-test-dir|COMMENT] to skip generation of the plugin [/test|COMMENT]
directory entirely:

  [./symfony generate:plugin sfExamplePlugin --skip-test-dir|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $plugin  = $arguments['plugin'];
    $modules = $options['module'];

    // validate the plugin name
    if ('Plugin' != substr($plugin, -6) || !preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $plugin))
    {
      throw new sfCommandException(sprintf('The plugin name "%s" is invalid.', $plugin));
    }

    // validate the test application name
    if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $options['test-application']))
    {
      throw new sfCommandException(sprintf('The application name "%s" is invalid.', $options['test-application']));
    }

    $this->checkPluginExists($plugin, false);

    if (is_readable(sfConfig::get('sf_data_dir').'/skeleton/plugin'))
    {
      $skeletonDir = sfConfig::get('sf_data_dir').'/skeleton/plugin';
    }
    else
    {
      $skeletonDir = dirname(__FILE__).'/skeleton/plugin';
    }

    $pluginDir   = sfConfig::get('sf_plugins_dir').'/'.$plugin;
    $testProject = $pluginDir.'/test/fixtures/project';
    $testApp     = $testProject.'/apps/'.$options['test-application'];

    $properties = parse_ini_file(sfConfig::get('sf_config_dir').'/properties.ini', true);
    $constants = array(
      'PLUGIN_NAME' => $plugin,
      'AUTHOR_NAME' => isset($properties['symfony']['author']) ? $properties['symfony']['author'] : 'Your name here',
      'APP_NAME'    => $options['test-application'],
    );

    // plugin
    $finder = sfFinder::type('any')->discard('.sf');
    $this->getFilesystem()->mirror($skeletonDir.'/plugin', $pluginDir, $finder);

    // PluginConfiguration
    $this->getFilesystem()->rename($pluginDir.'/config/PluginConfiguration.class.php', $pluginDir.'/config/'.$plugin.'Configuration.class.php');

    // tokens
    $finder = sfFinder::type('file')->name('*.php', '*.yml', 'package.xml.tmpl');
    $this->getFilesystem()->replaceTokens($finder->in($pluginDir), '##', '##', $constants);

    if ($options['skip-test-dir'])
    {
      sfToolkit::clearDirectory($pluginDir.'/test');
      $this->getFilesystem()->remove($pluginDir.'/test');
    }
    else
    {
      // test project and app
      $finder = sfFinder::type('any')->discard('.sf');
      $this->getFilesystem()->mirror(sfConfig::get('sf_symfony_lib_dir').'/task/generator/skeleton/project', $testProject, $finder);
      $this->getFilesystem()->mirror(sfConfig::get('sf_symfony_lib_dir').'/task/generator/skeleton/app/app', $testApp, $finder);

      // ProjectConfiguration
      $this->getFilesystem()->copy($skeletonDir.'/project/ProjectConfiguration.class.php', $testProject.'/config/ProjectConfiguration.class.php', array('override' => true));
      $this->getFileSystem()->replaceTokens($testProject.'/config/ProjectConfiguration.class.php', '##', '##', $constants);

      // ApplicationConfiguration
      $this->getFilesystem()->rename($testApp.'/config/ApplicationConfiguration.class.php', $testApp.'/config/'.$options['test-application'].'Configuration.class.php');
      $this->getFilesystem()->replaceTokens($testApp.'/config/'.$options['test-application'].'Configuration.class.php', '##', '##', $constants);

      // settings.yml
      $this->getFilesystem()->replaceTokens($testApp.'/config/settings.yml', '##', '##', array('NO_SCRIPT_NAME' => 'off', 'CSRF_SECRET' => $plugin, 'ESCAPING_STRATEGY' => 'on'));
    }

    // modules
    foreach ($modules as $module)
    {
      $moduleTask = new sfGeneratePluginModuleTask($this->dispatcher, $this->formatter);
      $moduleTask->setCommandApplication($this->commandApplication);
      $moduleTask->setConfiguration($this->configuration);
      $moduleTask->run(array($plugin, $module));
    }
  }
}
