<?php

$_SERVER['SYMFONY'] = '/Users/jwage/Sites/symfonysvn/1.3/lib';

if (!isset($_SERVER['SYMFONY']))
{
  throw new RuntimeException('Could not find symfony core libraries.');
}

require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    $sympalPluginPath = dirname(__FILE__).'/../../../..';
    $this->setPluginPath('sfSympalPlugin', $sympalPluginPath);

    require_once($sympalPluginPath.'/config/sfSympalPluginConfiguration.class.php');
    $dependencies = sfSympalPluginConfiguration::$dependencies;
    $embeddedPluginPath = $sympalPluginPath.'/lib/plugins';
    foreach ($dependencies as $plugin)
    {
      $this->setPluginPath($plugin, $embeddedPluginPath.'/'.$plugin);
    }

    $this->enableAllPluginsExcept(array('sfPropelPlugin', 'sfCompat10Plugin'));
  }

  /**
   * Methods used by unit.php and functional.php bootstrap files
   */

  public function initializeSympal()
  {
    chdir(sfConfig::get('sf_root_dir'));

    $task = new sfSympalInstallTask($this->dispatcher, new sfFormatter());
    $task->run(array(), array('--no-confirmation', '--application=sympal', '--env=test'));
  }

  public function loadFixtures($fixtures)
  {
    $fixtures = is_bool($fixtures) ? 'fixtures.yml' : $fixtures;
    $path = sfConfig::get('sf_data_dir') . '/fixtures/' . $fixtures;
    if ( ! file_exists($path)) {
      throw new sfException('Invalid data fixtures file');
    }
    chdir(sfConfig::get('sf_root_dir'));
    $task = new sfDoctrineLoadDataTask($this->dispatcher, new sfFormatter());
    $task->run(array(), array('--env=test', '--dir=' . $path));
  }
}
