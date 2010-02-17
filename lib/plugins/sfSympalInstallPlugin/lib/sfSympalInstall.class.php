<?php

class sfSympalInstall
{
  protected
    $_configuration,
    $_dispatcher,
    $_formatter,
    $_application = 'sympal',
    $_options = array(
      'force_reinstall' => false,
      'build_classes' => true
    ),
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

  public function getOption($key)
  {
    return $this->_options[$key];
  }

  public function getOptions()
  {
    return $this->_options;
  }

  public function setOption($key, $value)
  {
    $this->_options[$key] = $value;
  }

  public function setOptions(array $options)
  {
    $this->_options = $options;
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

    $this->_createWebCacheDirectory();
    $this->_prepareParams();

    if ($this->getOption('build_classes'))
    {
      $this->_buildAllClasses();
    }

    $dbExists = $this->checkSympalDatabaseExists();

    // If database does not exist or we are forcing a reinstall then lets do a full install
    if (!$dbExists || $this->getOption('force_reinstall'))
    {
      $this->_setupDatabase();
      $this->_loadData();
      $this->_createSite();
      $this->_installAddonPlugins();
      $this->_executePostInstallSql();
      $this->_executePostInstallHooks();
      $this->_publishAssets();
      $this->_clearCache();
      $this->_primeCache();

    // Delete site and recreate it
    } else {
      Doctrine_Manager::connection()->execute('delete from sf_sympal_site where slug = ?', array($this->_application));
      $this->_createSite();
      $this->_clearCache();
      $this->_primeCache();
    }

    sfSympalConfig::writeSetting('installed', true);
    sfSympalConfig::set('installing', false);
    sfSympalConfig::writeSetting('current_version', sfSympalPluginConfiguration::VERSION);

    $this->_dispatcher->notify(new sfEvent($this, 'sympal.post_install', array('configuration' => $this->_configuration, 'dispatcher' => $this->_dispatcher, 'formatter' => $this->_formatter)));

    // Run fix permissions to ensure a 100% ready to go environment
    $this->_fixPerms();
  }

  protected function _createWebCacheDirectory()
  {
    $dir = sfConfig::get('sf_web_dir').'/cache';
    if (!is_dir($dir))
    {
      mkdir($dir, 0777, true);
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

  protected function _createSite()
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

    sfSympalConfig::set('site_slug', $this->_application);
    $task = new sfDoctrineBuildTask($this->_dispatcher, $this->_formatter);
    $options = array(
      'db' => true,
      'no-confirmation' => true,
      'and-load' => false,
      'application' => $this->_application
    );

    $task->run(array(), $options);
  }

  protected function _getDataFixtures()
  {
    $path = sfConfig::get('sf_data_dir').'/fixtures/install';
    if (is_dir($path))
    {
      $fixtures = array(
        $this->_configuration->getPluginConfiguration('sfSympalPlugin')->getRootDir().'/data/fixtures/install.yml',
        $path
      );
    } else {
      $fixtures = true;
    }
    return $fixtures;
  }

  protected function _loadData($append = true)
  {
    sfSympalConfig::set('site_slug', $this->_application);
    $task = new sfDoctrineDataLoadTask($this->_dispatcher, $this->_formatter);
    $fixtures = $this->_getDataFixtures();
    if (!is_array($fixtures))
    {
      $fixtures = array();
    }
    $task->run($fixtures, array('append' => $append, 'application' => $this->_application));
  }
  
  protected function _installAddonPlugins()
  {
    $this->logSection('sympal', '...installing addon plugins', null, 'COMMENT');

    $plugins = $this->_configuration->getPluginConfiguration('sfSympalPlugin')->getSympalConfiguration()->getDownloadedPlugins();
    foreach ($plugins as $plugin)
    {
      $manager = sfSympalPluginManager::getActionInstance($plugin, 'install', $this->_configuration, $this->_formatter);

      // Don't need to publish assets, sympal install does this at the end
      $manager->setOption('publish_assets', false);

      // Don't need to clear cache, sympal install does this at the end
      $manager->setOption('clear_cache', false);

      // Don't need to uninstall first on sympal install
      $manager->setOption('uninstall_first', false);

      // Don't need to create tables as the sympal install already did this
      $manager->setOption('create_tables', false);

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