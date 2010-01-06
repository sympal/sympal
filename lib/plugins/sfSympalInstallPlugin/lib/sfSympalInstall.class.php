<?php

class sfSympalInstall
{
  protected
    $_configuration,
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

  public function logSection($section, $message, $size = null, $style = 'INFO')
  {
    $this->_configuration->getEventDispatcher()->notify(new sfEvent($this, 'command.log', array($this->_formatter->formatSection($section, $message, $size, $style))));
  }

  public function install()
  {
    $this->_dispatcher->notify(new sfEvent($this, 'sympal.pre_install', array('configuration' => $this->_configuration, 'dispatcher' => $this->_dispatcher, 'formatter' => $this->_formatter)));

    sfSympalConfig::set('installing', true);

    // Prepare the installation parameters
    $this->_prepareParams();

    // Setup/create the Sympal database
    $this->_setupDatabase();

    // Install Sympal to the database
    $this->_installSympal();

    // Run installation procss for any addon plugins
    $this->_installAddonPlugins();

    // Execute post install execute
    $this->_executePostInstallSql();

    // Execute post install hooks
    $this->_executePostInstallHooks();

    // Publish plugin assets
    $this->_publishAssets();

    // Clear the cache
    $this->_clearCache();

    // Prime the cache
    $this->_primeCache();

    // Run fix permissions to ensure a 100% ready to go environment
    $this->_fixPerms();

    sfSympalConfig::writeSetting('installed', true);
    sfSympalConfig::set('installing', false);
    sfSympalConfig::writeSetting('current_version', sfSympalPluginConfiguration::VERSION);

    $this->_dispatcher->notify(new sfEvent($this, 'sympal.post_install', array('configuration' => $this->_configuration, 'dispatcher' => $this->_dispatcher, 'formatter' => $this->_formatter)));
  }

  protected function _prepareParams()
  {
    foreach ($this->_params as $key => $value)
    {
      if ($value)
      {
        sfSympalConfig::set('sympal_install_admin_'.$key, $value);
      }
    }
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
      'and-load' => $fixtures,
      'application' => $this->_application
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
    $this->logSection('sympal', '...installing addon plugins', null, 'COMMENT');

    $plugins = $this->_configuration->getPluginConfiguration('sfSympalPlugin')->getSympalConfiguration()->getOtherPlugins();
    foreach ($plugins as $plugin)
    {
      $manager = sfSympalPluginManager::getActionInstance($plugin, 'install', $this->_configuration, $this->_formatter);
      $manager->setOption('publish_assets', false);
      $manager->install();
    }
  }

  protected function _executePostInstallSql()
  {
    $this->logSection('sympal', '...executing post install sql', null, 'COMMENT');

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
        $conn->exec($sql);
      }
    }
  }

  protected function _executePostInstallHooks()
  {
    if (method_exists($this->_configuration, 'install'))
    {
      $this->logSection('sympal', sprintf('...calling post install hook "%s::install()"', get_class($this->_configuration)), null, 'COMMENT');

      $this->_configuration->install();
    }
  }

  protected function _publishAssets()
  {
    chdir(sfConfig::get('sf_root_dir'));
    $task = new sfPluginPublishAssetsTask($this->_dispatcher, $this->_formatter);
    $task->run();
  }

  protected function _clearCache()
  {
    chdir(sfConfig::get('sf_root_dir'));
    $task = new sfCacheClearTask($this->_dispatcher, $this->_formatter);
    $task->run();
  }

  protected function _primeCache()
  {
    $autoload = sfSimpleAutoload::getInstance();
    $autoload->reload();
    $autoload->saveCache();
  }

  protected function _fixPerms()
  {
    chdir(sfConfig::get('sf_root_dir'));
    $task = new sfSympalFixPermsTask($this->_dispatcher, $this->_formatter);
    $task->run();
  }
}