<?php

class sfSympalInstall
{
  protected
    $_dispatcher,
    $_formatter;

  public function __construct(ProjectConfiguration $configuration, sfEventDispatcher $dispatcher, sfFormatter $formatter)
  {
    $this->_configuration = $configuration;
    $this->_dispatcher = $dispatcher;
    $this->_formatter = $formatter;
  }

  public function install()
  {
    $this->_dispatcher->notify(new sfEvent($this, 'sympal.pre_install', array('configuration' => $this->_configuration, 'dispatcher' => $this->_dispatcher, 'formatter' => $this->_formatter)));

    sfSympalConfig::set('installing', true);

    $this->_buildSympalInstallation();
    $this->_installSympalPlugins();
    $this->_executePostInstallSql();
    $this->_executePostInstallHooks();

    sfToolkit::clearGlob(sfConfig::get('sf_cache_dir'));

    sfSympalConfig::writeSetting('installed', true);
    sfSympalConfig::set('installing', false);

    $this->_dispatcher->notify(new sfEvent($this, 'sympal.post_install', array('configuration' => $this->_configuration, 'dispatcher' => $this->_dispatcher, 'formatter' => $this->_formatter)));
  }

  protected function _buildSympalInstallation()
  {
    $task = new sfDoctrineBuildTask($this->_dispatcher, $this->_formatter);
    $options = array('all' => true, 'no-confirmation' => true);

    if (file_exists(sfConfig::get('sf_data_dir').'/fixtures/install.yml'))
    {
      $options['and-load'] = sfConfig::get('sf_data_dir').'/fixtures';
    } else {
      $options['and-load'] = true;
    }

    $task->run(array(), $options);
  }

  protected function _installSympalPlugins($arguments = array(), $options = array())
  {
    $plugins = $this->_configuration->getPluginConfiguration('sfSympalPlugin')->getSympalConfiguration()->getOtherPlugins();
    foreach ($plugins as $plugin)
    {
      $manager = sfSympalPluginManager::getActionInstance($plugin, 'install', $this->_configuration, $this->_formatter);
      $manager->install();
    }
  }

  protected function _executePostInstallSql($arguments = array(), $options = array())
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