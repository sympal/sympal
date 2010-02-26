<?php

require_once dirname(__FILE__).'/sfTaskExtraGeneratorBaseTask.class.php';

/**
 * Wraps the generate module task to create a plugin module
 * 
 * @package     sfTaskExtraPlugin
 * @subpackage  task
 * @author      Kris Wallsmith <kris.wallsmith@symfony-project.com>
 * @version     SVN: $Id: sfGeneratePluginModuleTask.class.php 15353 2009-02-08 21:12:33Z Kris.Wallsmith $
 */
class sfGeneratePluginModuleTask extends sfTaskExtraGeneratorBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('plugin', sfCommandArgument::REQUIRED, 'The plugin name'),
      new sfCommandArgument('module', sfCommandArgument::REQUIRED, 'The module name'),
    ));

    $this->namespace = 'generate';
    $this->name = 'plugin-module';
    $this->briefDescription = 'Generates a new module in a plugin';

    $this->detailedDescription = <<<EOF
The [generate:plugin-module|INFO] task creates the basic directory structure
for a new module in an existing plugin:

  [./symfony generate:plugin-module sfExamplePlugin article|INFO]

You can customize the default skeleton used by the task by creating a
[%sf_data_dir%/skeleton/plugin_module|COMMENT] directory.

The task also creates a functional test stub in your plugin's
[/test/functional|COMMENT] directory.

If a module with the same name already exists in the plugin, a
[sfCommandException|COMMENT] is thrown.
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $plugin = $arguments['plugin'];
    $module = $arguments['module'];

    $this->checkPluginExists($plugin);

    // validate the module name
    if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $module))
    {
      throw new sfCommandException(sprintf('The module name "%s" is invalid.', $module));
    }

    $pluginDir = sfConfig::get('sf_plugins_dir').'/'.$plugin;
    $moduleDir = $pluginDir.'/modules/'.$module;
    $testDir   = $pluginDir.'/test';

    if (is_dir($moduleDir))
    {
      throw new sfCommandException(sprintf('The module "%s" already exists in the "%s" plugin.', $moduleDir, $plugin));
    }

    if (is_readable(sfConfig::get('sf_data_dir').'/skeleton/plugin_module'))
    {
      $skeletonDir = sfConfig::get('sf_data_dir').'/skeleton/plugin_module';
    }
    else
    {
      $skeletonDir = dirname(__FILE__).'/skeleton/plugin_module';
    }

    $properties = parse_ini_file(sfConfig::get('sf_config_dir').'/properties.ini', true);
    $constants = array(
      'PLUGIN_NAME' => $plugin,
      'MODULE_NAME' => $module,
      'AUTHOR_NAME' => isset($properties['symfony']['author']) ? $properties['symfony']['author'] : 'Your name here',
    );

    // create basic module structure
    $finder = sfFinder::type('any')->discard('.sf');
    $this->getFilesystem()->mirror($skeletonDir.'/module', $moduleDir, $finder);

    // rename base actions class
    $this->getFilesystem()->rename($moduleDir.'/lib/BaseActions.class.php', $moduleDir.'/lib/Base'.$module.'Actions.class.php');

    // customize php and yml files
    $finder = sfFinder::type('file')->name('*.php', '*.yml');
    $this->getFilesystem()->replaceTokens($finder->in($moduleDir), '##', '##', $constants);

    if (file_exists($testDir.'/fixtures/project/symfony'))
    {
      // create functional test
      $this->getFilesystem()->copy($skeletonDir.'/test/actionsTest.php', $testDir.'/functional/'.$module.'ActionsTest.php');
      $this->getFilesystem()->replaceTokens($testDir.'/functional/'.$module.'ActionsTest.php', '##', '##', $constants);

      // enable module in test project
      $file = $pluginDir.'/test/fixtures/project/config/settings.yml';
      $config = file_exists($file) ? sfYaml::load($file) : array();

      if (!isset($config['all']))
      {
        $config['all'] = array();
      }
      if (!isset($config['all']['enabled_modules']))
      {
        $config['all']['enabled_modules'] = array();
      }
      $config['all']['enabled_modules'][] = $module;

      $this->getFilesystem()->touch($file);
      file_put_contents($file, sfYaml::dump($config, 2));
    }
  }
}
