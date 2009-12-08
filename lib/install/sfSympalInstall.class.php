<?php

class sfSympalInstall
{
  protected
    $_dispatcher,
    $_formatter,
    $_application = 'sympal';

  public function __construct(ProjectConfiguration $configuration, sfEventDispatcher $dispatcher, sfFormatter $formatter)
  {
    $this->_configuration = $configuration;
    $this->_dispatcher = $dispatcher;
    $this->_formatter = $formatter;
  }

  public function setApplication($application)
  {
    $this->_application = $application;
  }

  public function install()
  {
    $this->_dispatcher->notify(new sfEvent($this, 'sympal.pre_install', array('configuration' => $this->_configuration, 'dispatcher' => $this->_dispatcher, 'formatter' => $this->_formatter)));

    sfSympalConfig::set('installing', true);

    $this->_setupDatabase();
    $this->_installSympal();
    $this->_installAddonPlugins();
    $this->_executePostInstallSql();
    $this->_executePostInstallHooks();

    sfToolkit::clearGlob(sfConfig::get('sf_cache_dir'));

    sfSympalConfig::writeSetting('installed', true);
    sfSympalConfig::set('installing', false);

    $this->_dispatcher->notify(new sfEvent($this, 'sympal.post_install', array('configuration' => $this->_configuration, 'dispatcher' => $this->_dispatcher, 'formatter' => $this->_formatter)));
  }

  protected function _setupDatabase()
  {
    $path = sfConfig::get('sf_config_dir').'/databases.yml';

    if (sfSympalConfig::get('sympal_install_database_dsn'))
    {
      $database = 'all:
  doctrine:
    class:  sfDoctrineDatabase
    param:
      dsn:        %s
      username:   %s
      password:   %s';

      $database = sprintf($database,
        sfSympalConfig::get('sympal_install_database_dsn'),
        sfSympalConfig::get('sympal_install_database_username'),
        sfSympalConfig::get('sympal_install_database_password')
      );

      $original = file($path);
      $databases = $database."\n\n".'#'.implode('#', $original);

      file_put_contents($path, $databases);

      try {
        $conn = Doctrine_Manager::getInstance()->openConnection(sfSympalConfig::get('sympal_install_database_dsn'), 'tmp', false);
        $conn->setOption('username', sfSympalConfig::get('sympal_install_database_username'));
        $conn->setOption('password', sfSympalConfig::get('sympal_install_database_password'));
        $conn->connect();
      } catch (Exception $e) {
        file_put_contents($path, implode('', $original));
        throw new InvalidArgumentException('Invalid database credentials specified, could not connect to database.');
      }
    }

    $path = sfConfig::get('sf_data_dir').'/fixtures/install';
    if (is_dir($path))
    {
      $fixtures = array();
      $fixtures[] = $path;
      $fixtures[] = $this->_configuration->getPluginConfiguration('sfSympalPlugin')->getRootDir().'/data/fixtures/install.yml';
      $fixtures[] = $this->_configuration->getPluginConfiguration('sfSympalUserPlugin')->getRootDir().'/data/fixtures/install.yml';
    } else {
      $fixtures = true;
    }

    sfSympalConfig::set('site_slug', $this->_application);
    $task = new sfDoctrineBuildTask($this->_dispatcher, $this->_formatter);
    $options = array(
      'db' => true,
      'sql' => true,
      'no-confirmation' => true,
      'and-load' => $fixtures
    );

    $task->run(array(), $options);
  }

  protected function _installSympal()
  {
    $task = new sfSympalCreateSiteTask($this->_dispatcher, $this->_formatter);
    $task->run(array('application' => $this->_application), array('no-confirmation' => true));  
  }

  protected function _installAddonPlugins()
  {
    $plugins = $this->_configuration->getPluginConfiguration('sfSympalPlugin')->getSympalConfiguration()->getOtherPlugins();
    foreach ($plugins as $plugin)
    {
      $manager = sfSympalPluginManager::getActionInstance($plugin, 'install', $this->_configuration, $this->_formatter);
      $manager->install();
    }
  }

  protected function _executePostInstallSql()
  {
    $dir = sfConfig::get('sf_data_dir').'/sql/sympal_install';
    if (is_dir($dir))
    {
      $this->_executeSqlFiles($dir);
    }

    $manager = Doctrine_Manager::getInstance();
    foreach ($manager as $conn)
    {
      $dir = sfConfig::get('sf_data_dir').'/sql/sympal_install/'.$conn->getName();
      if (is_dir($dir))
      {
        $this->_executeSqlFiles($dir, null);
      }
    }
  }

  protected function _executeSqlFiles($dir, $maxDepth = 0, $conn = null)
  {
    if (is_null($conn))
    {
      $conn = Doctrine_Manager::connection();
    }

    $files = sfFinder::type('file')
      ->name('*.sql')
      ->maxdepth($maxDepth)
      ->in($dir);

    foreach ($files as $file)
    {
      $sqls = file($file);
      foreach ($sqls as $sql)
      {
        $sql = trim($sql);
        $this->logSection('sympal', $sql);
        $conn->exec($sql);
      }
    }
  }

  protected function _executePostInstallHooks()
  {
    if (method_exists($this->_configuration, 'install'))
    {
      $this->_configuration->install();
    }
  }
}