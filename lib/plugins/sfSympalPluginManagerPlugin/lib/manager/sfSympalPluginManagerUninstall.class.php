<?php

class sfSympalPluginManagerUninstall extends sfSympalPluginManager
{
  protected $_options = array(
    'build_all_classes' => true,
    'delete_related_data' => true,
    'delete_other_data' => true,
    'drop_database_tables' => true,
    'publish_assets' => true,
    'delete_plugin_files' => false,
    'run_custom_install' => true,
  );

  public function uninstall($delete = null)
  {
    if ($delete !== null)
    {
      $this->setOption('delete_plugin_files', $delete);
    }

    $this->logSection('sympal', sprintf('Uninstalling Sympal plugin named "%s"', $this->_pluginName));

    if ($this->hasModels())
    {
      if ($this->getOption('build_all_classes'))
      {
        $this->_buildAllClasses();
      }
      if ($this->getOption('delete_related_data'))
      {
        $this->_deleteRelatedData();
      }
      if ($this->getOption('delete_other_data'))
      {
        $this->_deleteOtherData();
      }
      if ($this->getOption('drop_database_tables'))
      {
        $this->_dropDatabaseTables();
      }
    }

    if ($this->getOption('run_custom_install'))
    {
      $this->_runCustomUninstall();
    }

    if ($this->getOption('delete_plugin_files'))
    {
      $this->_deletePluginFiles();
    }

    if ($this->getOption('publish_assets'))
    {
      $this->_publishAssets();
    }

    sfSympalConfig::writeSetting($this->_pluginName, 'installed', false);
  }

  private function _deleteRelatedData()
  {
    if (!$this->_contentTypeName)
    {
      return;
    }

    $this->logSection('sympal', sprintf('...deleting data related to Sympal plugin ContentType "%s"', $this->_contentTypeName), null, 'COMMENT');

    $lowerName = str_replace('-', '_', Doctrine_Inflector::urlize($this->_contentTypeName));
    $slug = 'sample-'.$lowerName;

    $contentType = Doctrine_Core::getTable('sfSympalContentType')->findOneByName($this->_contentTypeName);

    // Delete content templates related to this content type
    $count = Doctrine_Core::getTable('sfSympalContentTemplate')
      ->createQuery('t')
      ->delete()
      ->where('t.content_type_id = ?', $contentType['id'])
      ->execute();

    // Find content lists related to this conten type
    $q = Doctrine_Core::getTable('sfSympalContentList')
      ->createQuery('c')
      ->select('c.id, c.content_id')
      ->from('sfSympalContentList c INDEXBY c.content_id')
      ->where('c.content_type_id = ?', $contentType['id']);

    $contentTypes = $q->fetchArray();
    $contentIds = array_keys($contentTypes);

    // Delete content records related to this content type
    Doctrine_Core::getTable('sfSympalContent')
      ->createQuery('c')
      ->delete()
      ->where('c.content_type_id = ?', $contentType['id'])
      ->orWhereIn('c.id', $contentIds)
      ->execute();

    // Delete menu items related to this content type
    Doctrine_Core::getTable('sfSympalMenuItem')
      ->createQuery('m')
      ->delete()
      ->where('m.name = ? OR m.content_type_id = ?', array($this->_contentTypeName, $contentType['id']))
      ->execute();

    // Delete the content type record
    Doctrine_Core::getTable('sfSympalContentType')
      ->createQuery('t')
      ->delete()
      ->where('t.id = ?', $contentType['id'])
      ->execute();
  }

  private function _deleteOtherData()
  {
    $this->logSection('sympal', '...deleting data for all models included in plugin', null, 'COMMENT');

    // Delete all data from models included in plugin
    foreach ($this->getPluginModelsInDeleteOrder() as $model)
    {
      try {
        if (class_exists($model))
        {
          Doctrine_Core::getTable($model)
            ->createQuery()
            ->delete()
            ->execute();

          $this->logSection('sympal', sprintf('Succcessfully truncated table for model named "%s"', $model));
        }
      } catch (Exception $e) {
        $this->logSection('sympal', 'Could not truncate table for model "'.$model.'": "'.$e->getMessage().'"');
      }
    }
  }

  private function _dropDatabaseTables()
  {
    $this->logSection('sympal', 'Dropping database tables for all plugin models');

    // Drop all tables
    foreach ($this->getPluginModelsInDeleteOrder() as $model)
    {
      try {
        if (class_exists($model))
        {
          $table = Doctrine_Core::getTable($model);

          $this->logSection('sympal', '...'.$table->getConnection()->export->dropTableSql($table->getTableName()), null, 'COMMENT');

          $table->getConnection()->export->dropTable($table->getTableName());
        }
      } catch (Exception $e) {
        $this->logSection('sympal', sprintf('...could not drop table named "%s": ', $table->getTableName()).$e->getMessage().'"', null, 'ERROR');
      }
    }
  }

  private function _runCustomUninstall()
  {
    if (method_exists($this, 'customUninstall'))
    {
      $this->logSection('sympal', 'Calling '.get_class($this).'::customUninstall()');

      $this->customUninstall();
    } else if (method_exists($this->_pluginConfig, 'customUninstall')) {
      $this->logSection('sympal', 'Calling '.get_class($this->_pluginConfig).'::customUninstall()');

      $this->_pluginConfig->customUninstall($this->_dispatcher, $this->_formatter);
    }
  }

  private function _deletePluginFiles()
  {
    $this->logSection('sympal', 'Removing plugin files');

    Doctrine_Lib::removeDirectories($this->_pluginPath);

    if ($this->_contentTypeName)
    {
      chdir(sfConfig::get('sf_root_dir'));
      $task = new sfDoctrineDeleteModelFilesTask($this->_dispatcher, $this->_formatter);
      foreach ($this->getPluginModels() as $model)
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

    if ($this->hasWebDirectory())
    {
      unlink(sfConfig::get('sf_web_dir').'/'.$this->_pluginName);
    }
  }
}