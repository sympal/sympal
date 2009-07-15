<?php

class sfSympalPluginManagerUninstall extends sfSympalPluginManager
{
  public function uninstall($delete = false)
  {
    $uninstallVars = array();

    $this->logSection('sympal', 'Uninstall sympal plugin named '.$this->_pluginName);

    $pluginPath = sfSympalPluginToolkit::getPluginPath($this->_pluginName);
    $schema = $pluginPath.'/config/doctrine/schema.yml';

    if (file_exists($schema))
    {
      $models = array_keys(sfYaml::load($schema));
      sfToolkit::clearGlob(sfConfig::get('sf_cache_dir'));

      if ($this->_contentTypeName)
      {
        $this->deleteRelatedRecords();
      }

      $this->logSection('sympal', 'Clear database tables of data');

      // Delete all data from models included in plugin
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
          $this->logSection('sympal', 'Could not truncate table for model "'.$model.'": "'.$e->getMessage().'"');
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
          $this->logSection('sympal', 'Could not drop table for model "'.$model.'": "'.$e->getMessage().'"');
        }
      }
    }

    $pluginConfig = $this->_configuration->getPluginConfiguration($this->_pluginName);

    if (method_exists($this, 'customUninstall'))
    {
      $this->logSection('sympal', 'Calling '.get_class($this).'::customUninstall()');

      $this->customUninstall($uninstallVars);
    }

    if ($delete)
    {
      $this->logSection('sympal', 'Removing plugin files');

      Doctrine_Lib::removeDirectories($pluginPath);

      if ($this->_contentTypeName)
      {
        chdir(sfConfig::get('sf_root_dir'));
        $task = new sfSympalDeleteModelTask($this->_dispatcher, $this->_formatter);
        foreach ($models as $model)
        {
          $task->run(array($model), array('--no-confirmation'));
        }
      }

      $path = sfConfig::get('sf_lib_dir').'/*/doctrine/'.$this->_pluginName;
      $dirs = glob($path);
      sfToolkit::clearGlob($path);
      foreach ($dirs as $dir)
      {
        Doctrine_Lib::removeDirectories($dir);
      }
    }

    $this->logSection('sympal', 'Clear cache');

    sfToolkit::clearGlob(sfConfig::get('sf_cache_dir'));

    if (file_exists($schema))
    {
      $this->rebuildFilesFromSchema();
    }

    if (is_dir($pluginPath.'/web'))
    {
      chdir(sfConfig::get('sf_root_dir'));
      $assets = new sfPluginPublishAssetsTask($this->_dispatcher, $this->_formatter);
      $ret = @$assets->run(array(), array());
    }

    sfSympalConfig::writeSetting($this->_pluginName, 'installed', false);
  }

  public function deleteRelatedRecords()
  {
    $this->logSection('sympal', 'Delete content from database');

    $lowerName = str_replace('-', '_', Doctrine_Inflector::urlize($this->_name));
    $slug = 'sample-'.$lowerName;

    $contentType = Doctrine::getTable('ContentType')->findOneByName($this->_contentTypeName);

    // Delete routes related to this content type
    Doctrine::getTable('Route')
      ->createQuery('r')
      ->delete()
      ->where('r.content_type_id = ?', $contentType['id'])
      ->execute();

    // Delete content templates related to this content type
    Doctrine::getTable('ContentTemplate')
      ->createQuery('t')
      ->delete()
      ->where('t.content_type_id = ?', $contentType['id'])
      ->execute();

    // Find content lists related to this conten type
    $q = Doctrine::getTable('ContentList')
      ->createQuery('c')
      ->select('c.id, c.content_id')
      ->from('ContentList c INDEXBY c.content_id')
      ->where('c.content_type_id = ?', $contentType['id']);

    $contentTypes = $q->fetchArray();
    $contentIds = array_keys($contentTypes);

    // Delete content records related to this content type
    Doctrine::getTable('Content')
      ->createQuery('c')
      ->delete()
      ->where('c.content_type_id = ?', $contentType['id'])
      ->orWhereIn('c.id', $contentIds)
      ->execute();

    // Delete menu items related to this content type
    Doctrine::getTable('MenuItem')
      ->createQuery('m')
      ->delete()
      ->where('m.name = ? OR m.content_type_id = ?', array($this->_name, $contentType['id']))
      ->execute();

    // Delete the content type record
    Doctrine::getTable('ContentType')
      ->createQuery('t')
      ->delete()
      ->where('t.id = ?', $contentType['id'])
      ->execute();
  }
}