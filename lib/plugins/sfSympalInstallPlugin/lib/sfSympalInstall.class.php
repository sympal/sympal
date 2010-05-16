<?php

/**
 * Main class that handles the sympal installation
 * 
 * @package     sfSympalInstallPlugin
 * @subpackage  task
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */
class sfSympalInstall
{
  protected
    $_configuration,
    $_dispatcher,
    $_formatter,
    $_application,
    $_params = array(
      'username' => 'admin',
      'password' => 'admin',
    ),
    $_trace = true; // by default, DO log all events

  /**
   * Internal variables to help if _trace = off
   * 
   * In those cases, we hijack the command.log event to control output
   */
  protected
    $_commandLogListeners = array(),
    $_consoleApplicationLogger,
    $_showLog = true;

  /**
   * Class constructor
   */
  public function __construct(ProjectConfiguration $configuration, sfEventDispatcher $dispatcher, sfFormatter $formatter)
  {
    $this->_configuration = $configuration;
    $this->_dispatcher = $dispatcher;
    $this->_formatter = $formatter;
    if ($app = sfConfig::get('sf_app'))
    {
      $this->_application = $app;
    }

    // control console logging
    $this->replaceLoggers();
  }
  
  /**
   * Actually runs the installation - this is the main entry point for
   * installing sympal
   */
  public function install()
  {
    // set params as sfConfig variables for global access
    $this->_prepareParams();
    
    // throw a sympal.pre_install event
    $this->_dispatcher->notify(new sfEvent($this, 'sympal.pre_install', array(
      'configuration' => $this->_configuration,
      'dispatcher' => $this->_dispatcher,
      'formatter' => $this->_formatter
    )));

    // The main installation stream
    $this->_setupDatabase();
    $this->_buildAllClasses();
    $this->_loadData();
    $this->_createSite();
    $this->_installAddonPlugins();
    $this->_executePostInstallSql();
    $this->_executePostInstallHooks();
    $this->_publishAssets();
    $this->_clearCache();
    $this->_primeCache();

    sfSympalConfig::writeSetting('installed', true);
    sfSympalConfig::writeSetting('current_version', sfSympalPluginConfiguration::VERSION);

    $this->_dispatcher->notify(new sfEvent($this, 'sympal.post_install', array('configuration' => $this->_configuration, 'dispatcher' => $this->_dispatcher, 'formatter' => $this->_formatter)));

    // Run fix permissions to ensure a 100% ready to go environment
    $this->_fixPerms();
  }

  /**
   * Sets the $_params array as sfConfig variables
   */
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

  /**
   * This copies all of the absolute base fixtures to the data/fixture/sympal directory
   */
  protected function _copyFixtures()
  {
    $this->logSection('fixtures', 'Coping base fixtures into data/fixture/sympal directory');

    $sympalFixturesPath = sfConfig::get('sf_data_dir').'/fixtures/sympal';
    if (!file_exists($sympalFixturesPath))
    {
      $this->logSection('fixtures', sprintf('Creating fixtures directory %s', $sympalFixturesPath));
      mkdir($sympalFixturesPath, 0777, true);
    }
    
    $yamls = sfFinder::type('file')
      ->name('*.yml.sample')
      ->in(sfConfig::get('sf_plugins_dir').'/sfSympalPlugin/data/fixtures/install');
    
    foreach ($yamls as $yaml)
    {
      // save it without the .sample
      $newFile = $sympalFixturesPath.'/'.str_replace('.sample', '', basename($yaml));

      if (file_exists($newFile))
      {
        $this->logSection('fixtures', 'Skipping file because it already exists '.$newFile);
      }
      else
      {
        // execute the yaml file into a variable
        ob_start();
        $retval = include($yaml);
        $content = ob_get_clean();

        $this->logSection('fixtures', 'Created file '.$newFile);
        file_put_contents($newFile, $content);
      }
    }
  }

  protected function _createSite()
  {
    chdir(sfConfig::get('sf_root_dir'));
    $task = new sfSympalCreateSiteTask($this->_dispatcher, $this->_formatter);
    
    $this->logTaskCall(
      'configure',
      'Configuring app for sympal',
      sprintf('sympal:create-site %s', $this->_application));
    
    $this->_disableOutput();
    $task->run(array($this->_application), array('no-confirmation' => true));
    $this->_enableOutput();
    
    $this->logSection('configure', '...finished configuration application');
  }

  /**
   * Builds all the doctrine classes
   */
  protected function _buildAllClasses()
  {
    chdir(sfConfig::get('sf_root_dir'));
    $task = new sfDoctrineBuildTask($this->_dispatcher, $this->_formatter);

    $this->logTaskCall(
      'build',
      'Building all classes',
      sprintf('doctrine:build --all-classes --application=%s', $this->_application)
    );

    $this->_disableOutput();
    $task->run(array(), array('all-classes', '--application='.sfConfig::get('sf_app')));
    $this->_enableOutput();
    
    $this->logSection('build', '...finished building all classes');
  }

  /**
   * This method performs the following tasks:
   *   * Creates the database
   *   * Builds the tables
   */
  protected function _setupDatabase()
  {
    $this->logSection('database', 'Testing the database connection...');
    try
    {
      $conn = Doctrine_Manager::getInstance()->getCurrentConnection();
      if (!$conn)
      {
        throw Exception();
      }

      /*
       * Try to create the database.
       * 
       * It may already exist (which is ok), so swallow the error. If the
       * error was something other than the database already existing,
       * the error will rethrow in ->connect()
       */
      try
      {
        $conn->createDatabase();
      }
      catch (Exception $e)
      {
      }

      $conn->connect();
    }
    catch (Exception $e)
    {
      if ($this->_trace)
      {
        throw $e;
      }
      else
      {
        throw new InvalidArgumentException('Could not make database connection: database connection not setup properly in databases.yml');
      }
    }
    $this->logSection('database', '...Database connection is setup correctly');

    $task = new sfDoctrineBuildTask($this->_dispatcher, $this->_formatter);
    $options = array(
      'db' => true,
      'no-confirmation' => true,
      'and-load' => false,
      'application' => $this->_application
    );

    $this->logTaskCall(
      'database',
      'Building database and tables',
      sprintf('doctrine:build --db --and-load=false --application=%s', $this->_application)
    );

    $this->_disableOutput();
    $task->run(array(), $options);
    $this->_enableOutput();
    
    $this->logSection('database', '...database and tables built successfully');
  }

  /**
   * Loads the project data
   */
  protected function _loadData($append = true)
  {
    sfConfig::set('sf_app', $this->_application);
    $task = new sfDoctrineDataLoadTask($this->_dispatcher, $this->_formatter);
    $this->logTaskCall('data', 'Loading fixture data', sprintf(
      'doctrine:data-load --append=%s --application=%s)',
      ($append) ? 'true' : 'false',
      $this->_application
    ));
    
    $this->_disableOutput();
    $task->run(array(), array('append' => $append, 'application' => $this->_application));
    $this->_enableOutput();
    
    $this->logSection('data', '...finished loading fixture data');
  }
  
  protected function _installAddonPlugins()
  {
    $this->logSection('plugins', '...installing addon plugins');

    $this->_disableOutput();
    $plugins = $this->_configuration->getPluginConfiguration('sfSympalPlugin')->getSympalConfiguration()->getDownloadedPlugins();
    foreach ($plugins as $plugin)
    {
      $this->logTaskCall(
        'plugins',
        'Installing %s',
        sprintf('(sympal:plugin-install %s --application=%s)', $plugin, $this->_application)
      );
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
    $this->_enableOutput();
    
    $this->logSection('plugins', '...plugins installed');
  }

  /**
   * Executes raw sql at the end of the installation process:
   * 
   *   * Executes all *.sql files in the data/sql/sympal_install dir
   *   * Executes all *.sql files for each individual database connection
   *     in the data/sql/sympal_install/##CONN_NAME## di r
   */
  protected function _executePostInstallSql()
  {
    $this->logSection('post-install', '...executing post install sql');

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

  /**
   * Execute all *.sql files inside the given directory
   * 
   * @param string $dir The absolute path to the dir with the *.sql files
   * @param integer $maxDepth The depth to look for files, defaults to only the immediate dir
   * @param Doctrine_Connection $conn The connection no which to execute
   */
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

  /**
   * Calls sfApplicationConfiguration->install() at the end of the install process.
   * 
   * In other words, if you create an install() task in
   * apps/MY_APP/config/MY_APPConfiguration.class.php then it will be called here.
   */
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
    
    $this->logTaskCall('plugins', 'Creating plugin symlinks', 'plugin:publish-assets');
    $this->_disableOutput();
    $task->run();
    $this->_enableOutput();
  }

  protected function _clearCache()
  {
    chdir(sfConfig::get('sf_root_dir'));
    $task = new sfCacheClearTask($this->_dispatcher, $this->_formatter);
    
    $this->logTaskCall('cache', 'Clearing cache', 'cache:clear');
    
    $this->_disableOutput();
    $task->run();
    $this->_enableOutput();
    
    $this->logSection('cache', '...finished clearing cache');
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
    
    $this->logTaskCall('permissions', 'Preparing sympal permissions', 'sympal:fix-perms');
    
    $this->_disableOutput();
    $task->run();
    $this->_enableOutput();
    
    $this->logSection('permissions', '...permissions set');
  }

  /*
   * Logging Functions
   */
  /**
   * Logs a message. If used in a task, will log to the terminal
   */
  public function logSection($section, $message, $size = null, $style = 'INFO')
  {    
    $this->_configuration->getEventDispatcher()->notify(new sfEvent($this, 'command.log', array($this->_formatter->formatSection($section, $message, $size, $style))));
  }

  /**
   * Logs the calling of a task, gives it a nice formatting
   * 
   * @param string $comment  The comment of what you're doing
   * @param string $taskCall The exact parameters of the task (e.g plugin:publish-assets)
   */
  public function logTaskCall($section, $comment, $taskCall)
  {
    $message = sprintf('%s (%s)...', $comment, $this->_formatter->format($taskCall, 'COMMENT'));
    
    $this->logSection($section, $message);
  }

  /**
   * We replace logging with our own to control output
   * 
   * Logging is handled by two events: console.log and application.log.
   * The first is the main way to log to the terminal, but application.og
   * is used by sfCommandLogger.
   * 
   * We remove the listeners the create console output and replace them
   * with our own. We can then control our own listener and turn on/off
   * console output. If we want output, then our listener simply acts as
   * its own dispatcher, dispatching the event to all of the previous listeners.
   */
  protected function replaceLoggers()
  {
    foreach ($this->_dispatcher->getListeners('command.log') as $listener)
    {
      $this->_commandLogListeners[] = $listener;
      $this->_dispatcher->disconnect('command.log', $listener);
    }
    
    $this->_dispatcher->connect('command.log', array($this, 'listenToLogEvent'));
    
    foreach ($this->_dispatcher->getListeners('application.log') as $listener)
    {
      if ($listener[0] instanceof sfCommandLogger)
      {
        $this->_consoleApplicationLogger = $listener;
        $this->_dispatcher->disconnect('application.log', $listener);
        $this->_dispatcher->connect('application.log', array($this, 'listenToLogEvent'));
        
        break;
      }
    }
  }

  /**
   * Overridden command.log event so we can surpress the output of other
   * tasks as we want to.
   * 
   * This checks on the _showLog property to see if it should really report
   * the log to the true listeners
   */
  public function listenToLogEvent(sfEvent $event)
  {
    // 41 is the red error color, if it is in the string, assume error and report
    $isError = (strpos($event[0], '41;') !== false);

    if ($this->_showLog || $isError)
    {
      if ($event->getName() == 'command.log')
      {
        foreach ($this->_commandLogListeners as $listener)
        {
          call_user_func($listener, $event);
        }
      }
      elseif ($event->getName() == 'application.log')
      {
        if ($this->_consoleApplicationLogger)
        {
          call_user_func($this->_consoleApplicationLogger, $event);
        }
      }
    }
  }

  /**
   * Will disable the command.log output unless trace is forcefully set to true
   */
  protected function _disableOutput()
  {
    if (!$this->_trace)
    {
      $this->_showLog = false;
    }
  }

  /**
   * Re-enables the command.log output
   */
  protected function _enableOutput()
  {
    $this->_showLog = true;
  }

  /**
   * Public getters and setters
   */

  public function getParam($key, $default = null)
  {
    return isset($this->_params[$key]) ? $this->_params[$key] : $default;
  }

  public function setParam($key, $value)
  {
    $this->_params[$key] = $value;
  }

  public function setApplication($application)
  {
    $this->_application = $application;
  }

  public function setConfiguration(ProjectConfiguration $configuration)
  {
    $this->_configuration = $configuration;
  }

  public function setTrace($bool)
  {
    $this->_trace = $bool;
  }
}