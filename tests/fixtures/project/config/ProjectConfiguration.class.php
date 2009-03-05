<?php

$_SERVER['SYMFONY'] = '/Users/jwage/Sites/symfony12svn/lib';

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
    $this->setPluginPath('sfSympalPlugin', dirname(__FILE__).'/../../../..');

    $plugins = array(
      'sfSympalCommentsPlugin',
      'sfSympalI18nPlugin',
      'sfSympalMenuPlugin',
      'sfSympalPagesPlugin',
      'sfSympalRegisterPlugin',
      'sfSympalSecurityPlugin',
      'sfDoctrineGuardPlugin',
      'sfFormExtraPlugin',
    );

    foreach ($plugins as $plugin)
    {
      $path = dirname(__FILE__).'/../../../../lib/plugins/';
      $this->setPluginPath($plugin, $path . $plugin);
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
    $task->run(array(), array('--no-confirmation', '--application=frontend', '--env=test'));
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