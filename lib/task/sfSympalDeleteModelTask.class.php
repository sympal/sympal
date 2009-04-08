<?php

class sfSympalDeleteModelTask extends sfTaskExtraBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'The name of the model you wish to delete all related files for.'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'delete-model';
    $this->briefDescription = 'Delete all the related auto generated files for a given model name.';

    $this->detailedDescription = <<<EOF
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $modelName = $arguments['name'];

    if (!$this->askConfirmation(array('This command will delete generated files related to the model named "'.$modelName.'"', 'Are you sure you want to proceed? (y/N)'), null, false))
    {
      $this->logSection('sympal', 'Delete model task aborted');

      return 1;
    }

    $names = array(
      $modelName.'.class.php',
      'Plugin'.$modelName.'.class.php',
      'Base'.$modelName.'.class.php',
      $modelName.'Form.class.php',
      'Plugin'.$modelName.'Form.class.php',
      'Base'.$modelName.'Form.class.php',
      $modelName.'FormFilter.class.php',
      'Plugin'.$modelName.'FormFilter.class.php',
      'Base'.$modelName.'FormFilter.class.php'
    );

    $pluginPaths = $this->configuration->getPluginPaths();
    $pluginLibDirs = sfFinder::type('dir')
      ->name('lib')
      ->maxdepth(1)
      ->in($pluginPaths);

    $in = array(
      sfConfig::get('sf_lib_dir'),
    );
    $in = array_merge($in, $pluginLibDirs);

    $files = sfFinder::type('file')
      ->name($names)
      ->in($in);

    if (empty($files))
    {
      throw new sfException('No files found for the model named "'.$modelName.'"');
    }

    $this->logSection('sympal', 'Found '.count($files).' files related to the model named "'.$modelName.'"');
    $this->log(null);
    foreach ($files as $file)
    {
      $this->log('  '.$file);
    }
    $this->log(null);
    if (!$this->askConfirmation(array('You are about to delete the above listed files!', 'Are you sure you want to proceed? (y/N)'), null, false))
    {
      $this->logSection('sympal', 'Delete model task aborted');

      return 1;
    }

    foreach ($files as $file)
    {
      $this->getFilesystem()->remove($file);
    }
  }
}