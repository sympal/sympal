<?php

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
    $this->enablePlugins(array('sfDoctrinePlugin'));
    require_once(dirname(__FILE__).'/../../../../config/sfSympalPluginConfiguration.class.php');
    sfSympalPluginConfiguration::enableSympalPlugins($this);
  }

  public function setupPlugins()
  {
    if (isset($this->pluginConfigurations['sfSympalPlugin']))
    {
      $this->pluginConfigurations['sfSympalPlugin']->connectTests();
    }
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
    copy(sfConfig::get('sf_data_dir').'/fresh_test_db.sqlite', sfConfig::get('sf_data_dir').'/test.sqlite');

    if (isset($this->pluginConfigurations['sfSympalPlugin']))
    {
      $this->pluginConfigurations['sfSympalPlugin']
        ->getSympalConfiguration()->getCacheManager()->primeCache(true);
    }
  }
}
