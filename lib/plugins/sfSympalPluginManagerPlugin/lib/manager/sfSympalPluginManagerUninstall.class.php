<?php

class sfSympalPluginManagerUninstall extends sfSympalPluginManager
{
  public function uninstall($name, $contentTypeName = null, $delete = false)
  {
    if (!$contentTypeName)
    {
      $contentTypeName = $this->getContentTypeForPlugin($name);
    } else {
      $contentTypeName = $this->getContentTypeForPlugin($name);
    }

    $pluginName = sfSympalTools::getLongPluginName($name);
    $name = sfSympalTools::getShortPluginName($name);

    sfSympalConfig::writeSetting($pluginName, 'installed', false);

    $uninstallVars = array();

    $this->logSection('sympal', 'Uninstall sympal plugin named '.$pluginName);

    $path = $this->configuration->getPluginConfiguration($pluginName)->getRootDir();
    $schema = $path.'/config/doctrine/schema.yml';

    if (file_exists($schema))
    {
      $dataFixtures = sfFinder::type('file')->in($path.'/data/fixtures/install.yml');
      $models = array_keys(sfYaml::load($schema));

      sfToolkit::clearGlob(sfConfig::get('sf_cache_dir'));

      if ($contentTypeName)
      {
        $this->logSection('sympal', 'Delete content from database');

        $lowerName = str_replace('-', '_', Doctrine_Inflector::urlize($name));
        $slug = 'sample-'.$lowerName;

        $contentType = Doctrine::getTable('ContentType')->findOneByName($contentTypeName);
        Doctrine::getTable('ContentTemplate')
          ->createQuery('t')
          ->delete()
          ->where('t.content_type_id = ?', $contentType['id'])
          ->execute();
        Doctrine::getTable('ContentType')
          ->createQuery('t')
          ->delete()
          ->where('t.name = ?', $contentTypeName)
          ->execute();
        Doctrine::getTable('Content')
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

    chdir(sfConfig::get('sf_root_dir'));
    $assets = new sfPluginPublishAssetsTask($this->dispatcher, $this->formatter);
    $ret = @$assets->run(array(), array());
  }
}