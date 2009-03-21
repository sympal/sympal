<?php

class sfSympalPluginUninstallTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'The name of the functionality. i.e. sfSympal#NAME#Plugin'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'sympal'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'plugin-uninstall';
    $this->briefDescription = 'Uninstall a sympal plugin to an existing sympal installation';

    $this->detailedDescription = <<<EOF
The [sympal:plugin-uninstall|INFO] is a task to uninstall a plugin to an existing sympal installation.
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    $name = $arguments['name'];
    $pluginName = 'sfSympal'.Doctrine_Inflector::classify(Doctrine_Inflector::tableize($name)).'Plugin';

    if (!$this->askConfirmation(array('This command will uninstall and remove the sympal plugin named '.$pluginName, 'Are you sure you want to proceed? (y/N)'), null, false))
    {
      $this->logSection('sympal', 'Plugin uninstall aborted');

      return 1;
    }

    $path = $this->configuration->getPluginConfiguration($pluginName)->getRootDir();
    $schema = $path.'/config/doctrine/schema.yml';
    $dataFixtures = sfFinder::type('file')->in($path.'/data/fixtures/install.yml');
    $models = array_keys(sfYaml::load($schema));
    $entityTypeName = current($models);

    sfToolkit::clearGlob(sfConfig::get('sf_cache_dir'));

    if (!class_exists($entityTypeName))
    {
      return;
    }

    $this->logSection('sympal', 'Uninstall sympal plugin named '.$pluginName);

    $this->logSection('sympal', 'Delete data from database');

    $lowerName = str_replace('-', '_', Doctrine_Inflector::urlize($name));
    $slug = 'sample-'.$lowerName;

    $entityType = Doctrine::getTable('EntityType')->findOneByName($entityTypeName);
    Doctrine::getTable('EntityTemplate')
      ->createQuery('t')
      ->delete()
      ->where('t.entity_type_id = ?', $entityType['id'])
      ->execute();
    Doctrine::getTable('EntityType')
      ->createQuery('t')
      ->delete()
      ->where('t.name = ?', $entityTypeName)
      ->execute();
    Doctrine::getTable('Entity')
      ->createQuery('e')
      ->delete()
      ->where('e.slug = ?', $slug)
      ->execute();
    Doctrine::getTable('MenuItem')
      ->createQuery('m')
      ->delete()
      ->where('m.name = ?', $name)
      ->execute();

    $this->logSection('sympal', 'Drop database tables');

    foreach ($models as $model)
    {
      $table = Doctrine::getTable($model);
      $table->getConnection()->export->dropTable($table->getTableName());
    }

    $this->logSection('sympal', 'Removing plugin files');

    Doctrine_Lib::removeDirectories($path);

    $path = sfConfig::get('sf_lib_dir').'/*/doctrine/'.$pluginName;
    $dirs = glob($path);
    sfToolkit::clearGlob($path);
    foreach ($dirs as $dir)
    {
      Doctrine_Lib::removeDirectories($dir);
    }

    $this->logSection('sympal', 'Clear cache');

    sfToolkit::clearGlob(sfConfig::get('sf_cache_dir'));
  }
}