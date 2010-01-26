<?php

class sfSympalInstall
{
  protected
    $_configuration,
    $_dispatcher,
    $_formatter,
    $_application = 'sympal',
    $_forceReinstall = false,
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

  public function setConfiguration(ProjectConfiguration $configuration)
  {
    $this->_configuration = $configuration;
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

  public function setForceReinstall($bool)
  {
    $this->_forceReinstall = $bool;
  }

  public function logSection($section, $message, $size = null, $style = 'INFO')
  {
    $this->_configuration->getEventDispatcher()->notify(new sfEvent($this, 'command.log', array($this->_formatter->formatSection($section, $message, $size, $style))));
  }
  
  /**
   * Actually runs the installation - this is the main entry point for
   * installing sympal
   */
  public function install()
  {
    $this->_dispatcher->notify(new sfEvent($this, 'sympal.pre_install', array('configuration' => $this->_configuration, 'dispatcher' => $this->_dispatcher, 'formatter' => $this->_formatter)));

    sfSympalConfig::set('installing', true);

    // Prepare the installation parameters
    $this->_prepareParams();

    // Build all classes
    $this->_buildAllClasses();

    $dbExists = $this->checkSympalDatabaseExists();

    // If database does not exist or we are forcing a reinstall then lets do a full install
    if (!$dbExists || $this->_forceReinstall)
    {
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
    // If db exists and site does not exist then lets create the site
    } else if ($dbExists && !$this->checkSympalSiteExists()) {
      $this->_createSite();
    // Delete site and recreate it
    } else {
      Doctrine_Manager::connection()->execute('delete from sf_sympal_site where slug = ?', array($this->_application));
      $this->_createSite();
    }

    if (!$dbExists || $this->_forceReinstall)
    {
      // Publish plugin assets
      $this->_publishAssets();

      // Clear the cache
      $this->_clearCache();

      // Prime the cache
      $this->_primeCache();
    }

    sfSympalConfig::writeSetting('installed', true);
    sfSympalConfig::set('installing', false);
    sfSympalConfig::writeSetting('current_version', sfSympalPluginConfiguration::VERSION);

    $this->_dispatcher->notify(new sfEvent($this, 'sympal.post_install', array('configuration' => $this->_configuration, 'dispatcher' => $this->_dispatcher, 'formatter' => $this->_formatter)));

    if (!$dbExists || $this->_forceReinstall)
    {
      // Run fix permissions to ensure a 100% ready to go environment
      $this->_fixPerms();
    }
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

  public function checkSympalSiteExists()
  {
    try {
      $conn = Doctrine_Manager::connection();
      $result = $conn->fetchColumn('select slug from sf_sympal_site where slug = ?', array($this->_application));
      $return = isset($result[0]) && $result[0] == $this->_application;
    } catch (Exception $e) { 
      $return = false;
    }
    $conn->close();
    
    return $return;
  }

  public function checkSympalDatabaseExists()
  {
    try {
      $conn = Doctrine_Manager::connection();
      $conn->fetchColumn('select slug from sf_sympal_site where slug = ?', array($this->_application));
      $return = true;
    } catch (Exception $e) {
      $return = false;
    }
    $conn->close();
    
    return $return;
  }

  private function _createSite()
  {
    chdir(sfConfig::get('sf_root_dir'));
    $task = new sfSympalCreateSiteTask($this->_dispatcher, $this->_formatter);
    $task->run(array($this->_application), array('no-confirmation' => true));
  }

  protected function _buildAllClasses()
  {
    $this->logSection('sympal', '...building all classes', null, 'COMMENT');

    chdir(sfConfig::get('sf_root_dir'));
    $task = new sfDoctrineBuildTask($this->_dispatcher, $this->_formatter);
    $task->run(array(), array('all-classes', '--application='.sfConfig::get('sf_app')));
  }
  
  /**
   * Method called during a fresh install (or a force reload)
   * 
   * This method performs the following tasks:
   *   * Writes the database dsn to databases.yml
   *   * Creates the database
   *   * Loads the install fixtures
   */
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
  
  /**
   * Method called in a fresh install or a force reload install
   * 
   * This method simply calls the sympal:create-site task and creates
   * a cache directory to store the minified css and js
   */
  protected function _installSympal()
  {
    $task = new sfSympalCreateSiteTask($this->_dispatcher, $this->_formatter);
    $task->run(array('application' => $this->_application), array('no-confirmation' => true));  

    $dir = sfConfig::get('sf_web_dir').'/cache';
    if (!is_dir($dir))
    {
      mkdir($dir, 0777, true);
    }
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