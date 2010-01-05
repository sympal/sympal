<?php

class sfSympalPluginManagerInstall extends sfSympalPluginManager
{
  protected $_options = array(
    'create_tables' => true,
    'load_data' => true,
    'publish_assets' => true,
    'uninstall_first' => true
  );

  public function install()
  {
    if ($this->getOption('uninstall_first'))
    {
      $uninstall = new sfSympalPluginManagerUninstall($this->_pluginName, $this->_configuration, $this->_formatter);
      $uninstall->setOption('publish_assets', false);
      $uninstall->uninstall();
    }

    $this->logSection('sympal', sprintf('Installing Sympal plugin named "%s"', $this->_pluginName));

    try {
      if ($this->getOption('create_tables'))
      {
        $this->_createDatabaseTables();
      }

      if ($this->getOption('load_data'))
      {
        $this->_loadData();
      }

      if ($this->getOption('publish_assets'))
      {
        $this->_publishAssets();
      }

      $this->_clearCache();

      sfSympalConfig::writeSetting($this->_pluginName, 'installed', true);
    } catch (Exception $e) {
      $uninstall = new sfSympalPluginManagerUninstall($this->_pluginName);
      $uninstall->uninstall();

      throw $e;
    }
  }

  protected function _createDatabaseTables()
  {
    if ($this->hasModels())
    {
      $this->_buildAllClasses();

      // Create all tables
      foreach ($this->getPluginModelsInInsertOrder() as $model)
      {
        $this->logSection('sympal', sprintf('...creating database table for: "%s"', $model), null, 'COMMENT');

        try {
          $table = Doctrine_Core::getTable($model);
          $conn = $table->getConnection();
          $sql = $conn->export->createTableSql($table->getTableName(), $table->getColumns());
          foreach ($sql as $key => $value)
          {
            $this->logSection('sympal', '...'.$value, null, 'COMMENT');
          }

          $conn->export->createTable($table->getTableName(), $table->getColumns());
        } catch (Exception $e) {
          $this->logSection('sympal', sprintf('...failed creating table for "%s": '.$e->getMessage(), $model), null, 'ERROR');
        }
      }
    }
  }

  protected function _loadData()
  {
    $installFixtures = $this->_pluginConfig->getRootDir().'/data/fixtures/install';
    if (is_dir($installFixtures))
    {
      $this->logSection('sympal', sprintf('...loading plugin installation data fixtures from: "%s"', $installFixtures), null, 'COMMENT');

      $task = new sfDoctrineDataLoadTask($this->_dispatcher, $this->_formatter);
      $task->run(array($installFixtures), array('application' => sfConfig::get('sf_app')));
    }

    $installVars = array();

    if ($this->_contentTypeName)
    {
      $this->_createDefaultContentTypeRecords($installVars);
    }

    if (method_exists($this, 'customInstall'))
    {
      $this->logSection('sympal', sprintf('...executing %s::customInstall() method instead of default installation', get_class($this)), null, 'COMMENT');

      $this->customInstall($installVars);
    } else if (method_exists($this->_pluginConfig, 'customInstall')) {
        $this->logSection('sympal', sprintf('...executing %s::customInstall() method instead of default installation', get_class($this->_pluginConfig)), null, 'COMMENT');

        $this->_pluginConfig->customInstall($installVars, $this->_dispatcher, $this->_formatter);
    } else {
      if (isset($this->_contentTypeName) && $this->_contentTypeName)
      {
        $this->_defaultInstallation($installVars, $this->_contentTypeName);
      }
    }
  }

  protected function _createDefaultContentTypeRecords(&$installVars)
  {
    $this->logSection('sympal', '...creating default Sympal ContentType records', null, 'COMMENT');

    $lowerName = str_replace('-', '_', Doctrine_Inflector::urlize(str_replace('sfSympal', null, $this->_contentTypeName)));
    $slug = 'sample_'.$lowerName;

    $properties = array(
      'plugin_name' => $this->_pluginName,
    );

    $contentType = $this->newContentType($this->_contentTypeName, $properties);
    $installVars['contentType'] = $contentType;

    $properties = array(
      'slug' => $slug
    );

    $content = $this->newContent($contentType, $properties);
    $installVars['content'] = $content;

    $properties = array(
      'slug' => $lowerName,
      'ContentType' => Doctrine_Core::getTable('sfSympalContentType')->findOneByName('ContentList')
    );

    $contentList = $this->newContent('sfSympalContentList', $properties);
    $contentList->trySettingTitleProperty('Sample '.$contentType['label'].' List');
    $contentList->getRecord()->setContentType($contentType);
    $installVars['contentList'] = $contentList;

    $properties = array(
      'date_published' => new Doctrine_Expression('NOW()'),
      'label' => str_replace('sfSympal', null, $this->_contentTypeName),
      'RelatedContent' => $contentList
    );

    $menuItem = $this->newMenuItem($this->_contentTypeName, $properties);
    $installVars['menuItem'] = $menuItem;

    $properties = array(
      'body' => '<?php echo get_sympal_breadcrumbs($menuItem, $content) ?><h2><?php echo get_sympal_content_slot($content, \'title\') ?></h2><p><strong>Posted by <?php echo $content->CreatedBy->username ?> on <?php echo get_sympal_content_slot($content, \'date_published\') ?></strong></p><p><?php echo get_sympal_content_slot($content, \'body\') ?></p>',
    );
  }

  protected function _defaultInstallation($installVars)
  {
    $this->logSection('sympal', '...executing default installation', null, 'COMMENT');

    if (method_exists($this->_pluginConfig, 'filterInstallVars'))
    {
      $this->logSection('sympal', sprintf('...executing %s::filterInstallVars() method', get_class($this->_pluginConfig)), null, 'COMMENT');

      $this->_pluginConfig->filterInstallVars($installVars);
    } else {
      $this->logSection('sympal', sprintf('...executing %s::filterInstallVars() method', get_class($this)), null, 'COMMENT');

      $this->filterInstallVars($installVars);
    }

    $this->saveMenuItem($installVars['menuItem']);

    if (isset($installVars['contentType']))
    {
      $installVars['contentType']->save();
    }
    if (isset($installVars['contentList']))
    {
      $installVars['contentList']->save();
    }
    if (isset($installVars['content']))
    {
      $installVars['content']->save();
    }
  }

  public function filterInstallVars(array &$installVars)
  {
  }
}