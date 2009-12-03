<?php

$_SERVER['SYMFONY'] = '/Users/jwage/Sites/symfonysvn/1.4/lib';

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

  public function configureDoctrine(Doctrine_Manager $manager)
  {
    $manager->setAttribute(Doctrine_Core::ATTR_VALIDATE, Doctrine_Core::VALIDATE_ALL);
  }

  /**
   * Methods used by unit.php and functional.php bootstrap files
   */

  public function initializeSympal()
  {
    chdir(sfConfig::get('sf_root_dir'));

    $install = new sfSympalInstall($this, $this->dispatcher, new sfFormatter());
    $install->install();

    $cache = new sfSympalCache(
      $this->getPluginConfiguration('sfSympalPlugin')
           ->getSympalConfiguration()
    );
  }
}
