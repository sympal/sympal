<?php

class sfSympalInstall
{
  protected
    $_dispatcher,
    $_formatter,
    $_application = 'sympal',
    $_params = array(
      'db_dsn' => null,
      'db_username' => null,
      'db_password' => null,
      'username' => 'admin',
      'password' => 'admin',
      'first_name' => 'Sympal',
      'last_name' => 'Admin',
      'email_address' => 'admin@sympalphp.org',
    );

  public function __construct(ProjectConfiguration $configuration, sfEventDispatcher $dispatcher, sfFormatter $formatter)
  {
    $this->_configuration = $configuration;
    $this->_dispatcher = $dispatcher;
    $this->_formatter = $formatter;
    if ($app = sfConfig::get('sf_app'))
    {
      $this->_application = $app;
    }
  }

  public function setApplication($application)
  {
    $this->_application = $application;
  }

  public function getParam($key)
  {
    return $this->_params[$key];
  }

  public function getParams()
  {
    return $this->_params;
  }

  public function setParam($key, $value)
  {
    $this->_params[$key] = $value;
  }

  public function setParams(array $params)
  {
    $this->_params = $params;
  }

  public function install()
  {
    $this->_dispatcher->notify(new sfEvent($this, 'sympal.pre_install', array('configuration' => $this->_configuration, 'dispatcher' => $this->_dispatcher, 'formatter' => $this->_formatter)));

    sfSympalConfig::set('installing', true);

    foreach ($this->_params as $key => $value)
    {
      if ($value)
      {
        sfSympalConfig::set('sympal_install_admin_'.$key, $value);
      }
    }

    $this->_setupDatabase();
    $this->_installSympal();
    $this->_installAddonPlugins();
    $this->_executePostInstallSql();
    $this->_executePostInstallHooks();

    $task = new sfPluginPublishAssetsTask($this->_dispatcher, $this->_formatter);
    $task->run();

    sfToolkit::clearGlob(sfConfig::get('sf_cache_dir'));

    sfSympalConfig::writeSetting('installed', true);
    sfSympalConfig::set('installing', false);
    sfSympalConfig::writeSetting('current_version', sfSympalPluginConfiguration::VERSION);

    $this->_dispatcher->notify(new sfEvent($this, 'sympal.post_install', array('configuration' => $this->_configuration, 'dispatcher' => $this->_dispatcher, 'formatter' => $this->_formatter)));
  }

  protected function _setupDatabase()
  {
    if (isset($this->_params['db_dsn']) && isset($this->_params['db_username']))
    {
      $task = new sfDoctrineConfigureDatabaseTask($this->_dispatcher, $this->_formatter);
      $task->run(array(
        'dsn' => $this->_params['db_dsn'],
        'username' => $this->_params['db_username'],
        'password' => $this->_params['db_password']
      ));

      try {
        $conn = Doctrine_Manager::getInstance()->openConnection($this->_params['db_dsn'], 'test', false);
        $conn->setOption('username', $this->_params['db_username']);
        $conn->setOption('password', $this->_params['db_password']);

        try {
          $conn->createDatabase();
        } catch (Exception $e) {}

        $conn->connect();
      } catch (Exception $e) {
        throw new InvalidArgumentException('Database credentials are not valid!');
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
      'all' => true,
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