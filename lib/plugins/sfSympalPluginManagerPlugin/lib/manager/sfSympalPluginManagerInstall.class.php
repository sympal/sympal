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
      $this->logSection('sympal', sprintf('Uninstalling Sympal plugin named "%s" before installing to ensure a fresh environment to install to.', $this->_pluginName));

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

      $this->logSection('sympal', 'Creating database tables for all plugin models:');

      $models = $this->getPluginModels();
      foreach ($models as $model)
      {
        $this->logSection('sympal', $model);
      }

      Doctrine_Core::createTablesFromArray($models);
    }
  }

  protected function _loadData()
  {
    $installFixtures = $this->_pluginConfig->getRootDir().'/data/fixtures/install';
    if (is_dir($installFixtures))
    {
      $this->logSection('sympal', sprintf('Loading plugin installation data fixtures from: "%s"', $installFixtures));
      $task = new sfDoctrineDataLoadTask($this->_dispatcher, $this->_formatter);
      $task->run(array($installFixtures), array());
    }

    $installVars = array();

    if ($this->_contentTypeName)
    {
      $this->_createDefaultContentTypeRecords($installVars);
    }

    if (method_exists($this, 'customInstall'))
    {
      $this->logSection('sympal', sprintf('Executing %s::customInstall() method instead of default installation', get_class($this)));

      $this->customInstall($installVars);
    } else if (method_exists($this->_pluginConfig, 'customInstall')) {
        $this->logSection('sympal', sprintf('Executing %s::customInstall() method instead of default installation', get_class($this->_pluginConfig)));

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
    $this->logSection('sympal', 'Creating default Sympal ContentType records');

    $lowerName = str_replace('-', '_', Doctrine_Inflector::urlize($this->_contentTypeName));
    $slug = 'sample_'.$lowerName;

    $properties = array(
      'default_path' => "/$lowerName/:slug",
      'slug' => $lowerName,
      'plugin_name' => $this->_pluginName,
    );

    $contentType = $this->newContentType($this->_contentTypeName, $properties);
    $installVars['contentType'] = $contentType;

    $properties = array(
      'slug' => $slug,
      'is_published' => true
    );

    $content = $this->newContent($contentType, $properties);
    $installVars['content'] = $content;

    $properties = array(
      'slug' => $lowerName,
      'ContentType' => Doctrine_Core::getTable('sfSympalContentType')->findOneByName('ContentList')
    );

    $contentList = $this->newContent('ContentList', $properties);
    $contentList->trySettingTitleProperty('Sample '.$contentType['label'].' List');
    $contentList->getRecord()->setContentType($contentType);
    $installVars['contentList'] = $contentList;

    $properties = array(
      'is_published' => true,
      'label' => $this->_contentTypeName,
      'ContentType' => $contentType,
      'RelatedContent' => $contentList
    );

    $menuItem = $this->newMenuItem($this->_contentTypeName, $properties);
    $installVars['menuItem'] = $menuItem;

    $properties = array(
      'body' => '<?php echo get_sympal_breadcrumbs($menuItem, $content) ?><h2><?php echo get_sympal_column_content_slot($content, \'title\') ?></h2><p><strong>Posted by <?php echo $content->CreatedBy->username ?> on <?php echo get_sympal_column_content_slot($content, \'date_published\') ?></strong></p><p><?php echo get_sympal_column_content_slot($content, \'body\') ?></p>',
    );

    $contentTemplate = $this->newContentTemplate('View '.$this->_contentTypeName, $contentType, $properties);
    $installVars['contentTemplate'] = $contentTemplate;
  }

  protected function _defaultInstallation($installVars)
  {
    $this->logSection('sympal', 'Executing default installation');

    if (method_exists($this->_pluginConfig, 'filterInstallVars'))
    {
      $this->logSection('sympal', sprintf('Executing %s::filterInstallVars() method', get_class($this->_pluginConfig)));

      $this->_pluginConfig->filterInstallVars($installVars);
    } else {
      $this->logSection('sympal', sprintf('Executing %s::filterInstallVars() method', get_class($this)));

      $this->filterInstallVars($installVars);
    }

    $this->saveMenuItem($installVars['menuItem']);

    if (isset($installVars['contentType']))
    {
      $installVars['contentType']->save();
    }
    if (isset($installVars['contentTemplate']))
    {
      $installVars['contentTemplate']->save();
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