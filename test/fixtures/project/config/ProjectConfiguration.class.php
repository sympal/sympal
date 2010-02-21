<?php

if (!isset($_SERVER['SYMFONY']))
{
  // try to autodetect symfony path
  // assumed that the script is running from symfony dir
  // we don't use __FILE__ because it resolves symlinks
  if (is_readable('config/ProjectConfiguration.class.php'))
  {
    $matches = array();
    preg_match('/require_once.*sfCoreAutoload.class.php.*/', file_get_contents('config/ProjectConfiguration.class.php'), $matches);
    if (!empty($matches))
    {
      eval($matches[0]);
    }
    unset($matches);
  }
  else
  {
    throw new RuntimeException('Could not find symfony core libraries.');
  }
}
else
{
  require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
}


sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
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
        ->getSympalConfiguration()->getCache()->primeCache(true);
    }
  }
}
