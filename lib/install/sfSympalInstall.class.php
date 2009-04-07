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
    $this->_buildSympalInstallation();
    $this->_installSympalPlugins();
    $this->_executePostInstallSql();
    $this->_executePostInstallHooks();

    sfToolkit::clearGlob(sfConfig::get('sf_cache_dir'));
  }

  protected function _buildSympalInstallation()
  {
    $dropDb = new sfDoctrineDropDbTask($this->_dispatcher, $this->_formatter);
    $dropDbOptions = array();
    $dropDbOptions[] = '--no-confirmation';
    $dropDbOptions[] = '--env='.sfConfig::get('sf_environment');
    $dropDb->run(array(), $dropDbOptions);

    $buildAllLoad = new sfDoctrineBuildAllLoadTask($this->_dispatcher, $this->_formatter);
    $buildAllLoadOptions = array();
    $buildAllLoadOptions[] = '--env='.sfConfig::get('sf_environment');
    if (file_exists(sfConfig::get('sf_data_dir').'/fixtures/install.yml'))
    {
      $buildAllLoadOptions[] = '--dir='.sfConfig::get('sf_data_dir').'/fixtures';
    }
    $buildAllLoad->run(array(), $buildAllLoadOptions);
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