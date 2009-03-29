<?php

class sfSympalInstallTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'sympal'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
      new sfCommandOption('skip-forms', 'F', sfCommandOption::PARAMETER_NONE, 'Skip generating forms'),
      new sfCommandOption('dir', null, sfCommandOption::PARAMETER_REQUIRED | sfCommandOption::IS_ARRAY, 'The directories to look for fixtures'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'install';
    $this->briefDescription = 'Install the sympal plugin content management framework.';

    $this->detailedDescription = <<<EOF
The [sympal:install|INFO] task is a shortcut for five other tasks:

  [./sympal:install|INFO]

The task is equivalent to:

  [./symfony doctrine:drop-db|INFO]
  [./symfony doctrine:build-db|INFO]
  [./symfony doctrine:build-model|INFO]
  [./symfony doctrine:insert-sql|INFO]
  [./symfony doctrine:data-load|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->_buildSympalInstallation($arguments, $options);
    $this->_installSympalPlugins($arguments, $options);
    $this->_executePostInstallSql($arguments, $options);
  }

  protected function _buildSympalInstallation($arguments = array(), $options = array())
  {
    $dropDb = new sfDoctrineDropDbTask($this->dispatcher, $this->formatter);
    $dropDb->setCommandApplication($this->commandApplication);

    $dropDbOptions = array();
    $dropDbOptions[] = '--env='.$options['env'];
    if (isset($options['no-confirmation']) && $options['no-confirmation'])
    {
      $dropDbOptions[] = '--no-confirmation';
    }
    if (isset($options['application']) && $options['application'])
    {
      $dropDbOptions[] = '--application=' . $options['application'];
    }
    $dropDb->run(array(), $dropDbOptions);

    $buildAllLoad = new sfDoctrineBuildAllLoadTask($this->dispatcher, $this->formatter);
    $buildAllLoad->setCommandApplication($this->commandApplication);

    $buildAllLoadOptions = array();
    $buildAllLoadOptions[] = '--env='.$options['env'];
    if (!empty($options['dir']))
    {
      $buildAllLoadOptions[] = '--dir=' . implode(' --dir=', $options['dir']);
    }
    if (isset($options['append']) && $options['append'])
    {
      $buildAllLoadOptions[] = '--append';
    }
    if (isset($options['application']) && $options['application'])
    {
      $buildAllLoadOptions[] = '--application=' . $options['application'];
    }
    if (isset($options['skip-forms']) && $options['skip-forms'])
    {
      $buildAllLoadOptions[] = '--skip-forms';
    }
    if (file_exists(sfConfig::get('sf_data_dir').'/fixtures/install.yml') && !$options['dir'])
    {
      $buildAllLoadOptions[] = '--dir='.sfConfig::get('sf_data_dir').'/fixtures';
    }
    $buildAllLoad->run(array(), $buildAllLoadOptions);
  }

  protected function _installSympalPlugins($arguments = array(), $options = array())
  {
    $plugins = $this->configuration->getPluginConfiguration('sfSympalPlugin')->getSympalConfiguration()->getOtherPlugins();
    foreach ($plugins as $plugin)
    {
      $manager = sfSympalPluginManager::getActionInstance($plugin, 'install', $this->configuration, $this->formatter);
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
}