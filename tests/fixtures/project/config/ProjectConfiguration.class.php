<?php

if (!isset($_SERVER['SYMFONY']))
{
  $_SERVER['SYMFONY'] = '/Users/jwage/Sites/symfonysvn/1.4/lib';
}

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
    require_once(dirname(__FILE__).'/../../../../config/sfSympalPluginConfiguration.class.php');
    sfSympalPluginConfiguration::enableSympalPlugins($this);

    $this->enableAllPluginsExcept('sfPropelPlugin');
  }

  public function configureDoctrineConnection(Doctrine_Connection $conn)
  {
      $conn->setCollate('utf8_unicode_ci');
  }

  /**
   * Methods used by unit.php and functional.php bootstrap files
   */

  public function initializeSympal()
  {
    chdir(sfConfig::get('sf_root_dir'));

    $this->getPluginConfiguration('sfSympalPlugin')
      ->getSympalConfiguration()->getCache()->primeCache(true);
  }
}
