<?php

class sfSympalPluginManagerUninstall extends sfSympalPluginManager
{
  public function uninstall($name, $entityTypeName = null, $delete = false)
  {
    if (!$entityTypeName)
    {
      $entityTypeName = $this->getEntityTypeForPlugin($name);
    } else {
      $entityTypeName = $this->getEntityTypeForPlugin($name);
    }

    $pluginName = sfSympalTools::getLongPluginName($name);
    $name = sfSympalTools::getShortPluginName($name);

    $uninstallVars = array();

    $this->logSection('sympal', 'Uninstall sympal plugin named '.$pluginName);

    $path = $this->configuration->getPluginConfiguration($pluginName)->getRootDir();
    $schema = $path.'/config/doctrine/schema.yml';

    if (file_exists($schema))
    {
      $dataFixtures = sfFinder::type('file')->in($path.'/data/fixtures/install.yml');
      $models = array_keys(sfYaml::load($schema));

      sfToolkit::clearGlob(sfConfig::get('sf_cache_dir'));

      if ($entityTypeName)
      {
        $this->logSection('sympal', 'Delete entity from database');

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

        $this->logSection('sympal', 'Clear database tables of data');

        // Delete all data
        foreach ($models as $model)
        {
          try {
            if (class_exists($model))
            {
              Doctrine::getTable($model)
                ->createQuery()
                ->delete()
                ->execute();
            }
          } catch (Exception $e) {
            continue;
          }
        }

        $this->logSection('sympal', 'Drop database tables');

        // Drop all tables
        foreach ($models as $model)
        {
          try {
            if (class_exists($model))
            {
              $table = Doctrine::getTable($model);
              $table->getConnection()->export->dropTable($table->getTableName());
            }
          } catch (Exception $e) {
            continue;
          }
        }
      }
    }

    $pluginConfig = $this->configuration->getPluginConfiguration($pluginName);

    $createUninstall = false;
    if (method_exists($pluginConfig, 'uninstall'))
    {
      $this->logSection('sympal', 'Calling '.$pluginName.'Configuration::uninstall()');

      $pluginConfig->uninstall($uninstallVars, $this);
    } else {
      $createUninstall = true;
    }

    if ($delete)
    {
      $this->logSection('sympal', 'Removing plugin files');

      Doctrine_Lib::removeDirectories($path);

      $path = sfConfig::get('sf_lib_dir').'/*/doctrine/'.$pluginName;
      $dirs = glob($path);
      sfToolkit::clearGlob($path);
      foreach ($dirs as $dir)
      {
        Doctrine_Lib::removeDirectories($dir);
      }
    }

    $this->logSection('sympal', 'Clear cache');

    sfToolkit::clearGlob(sfConfig::get('sf_cache_dir'));

    if (isset($createUninstall) && $createUninstall)
    {
      $this->logSection('sympal', 'On the '.$pluginName.'Configuration class you can define a uninstall() method to perform additional uninstall operaitons for your sympal plugin!');
    }
  }
}