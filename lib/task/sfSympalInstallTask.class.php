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
}