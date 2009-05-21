<?php

class sfSympalPluginManagerInstall extends sfSympalPluginManager
{
  public $configuration;

  public function install()
  {
    $uninstall = new sfSympalPluginManagerUninstall($this->_pluginName, $this->_configuration, $this->_formatter);
    $uninstall->uninstall();

    try {
      $pluginPath = sfSympalPluginToolkit::getPluginPath($this->_pluginName);
      $schema = $pluginPath.'/config/doctrine/schema.yml';

      $installVars = array();
      if (file_exists($schema))
      {
        $dataFixtures = sfFinder::type('file')->in($pluginPath.'/data/fixtures/install.yml');
        $models = array_keys(sfYaml::load($schema));

        $this->rebuildFilesFromSchema();

        $this->logSection('sympal', 'Create the tables for the models');

        Doctrine::createTablesFromArray($models);

        if ($this->_contentTypeName)
        {
          $installVars = $this->_createDefaultContentTypeRecords($installVars);
        }
      }

      if (method_exists($this, 'customInstall'))
      {
        $this->logSection('sympal', 'Calling '.get_class($this).'::customInstall()');

        $this->customInstall($installVars);
      } else {
        if (isset($this->_contentTypeName) && $this->_contentTypeName)
        {
          $this->_defaultInstallation($installVars, $this->_contentTypeName);
        }
      }

      if (is_dir($pluginPath.'/web'))
      {
        chdir(sfConfig::get('sf_root_dir'));
        $assets = new sfPluginPublishAssetsTask($this->_dispatcher, $this->_formatter);
        $ret = @$assets->run(array(), array());
      }

      sfSympalConfig::writeSetting($this->_pluginName, 'installed', true);
    } catch (Exception $e) {
      $uninstall = new sfSympalPluginManagerUninstall($this->_pluginName);
      $uninstall->uninstall();

      throw $e;
    }
  }

  protected function _createDefaultContentTypeRecords($installVars)
  {
    $this->logSection('sympal', 'Create default content type records');

    $lowerName = str_replace('-', '_', Doctrine_Inflector::urlize($this->_name));
    $slug = 'sample_'.$lowerName;

    $properties = array(
      'default_path' => "/$lowerName/:slug",
      'slug' => $lowerName,
      'plugin_name' => $this->_pluginName,
      'Site' => Doctrine::getTable('Site')->findOneBySlug(sfConfig::get('app_sympal_config_site_slug', sfConfig::get('sf_app')))
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
      'ContentType' => Doctrine::getTable('ContentType')->findOneByName('ContentList')
    );

    $contentList = $this->newContent('ContentList', $properties);
    $contentList->trySettingTitleProperty('Sample '.$contentType['label'].' List');
    $contentList->getRecord()->setContentType($contentType);
    $installVars['contentList'] = $contentList;

    $properties = array(
      'is_published' => true,
      'label' => $this->_name,
      'ContentType' => $contentType,
      'RelatedContent' => $contentList
    );

    $menuItem = $this->newMenuItem($this->_name, $properties);
    $installVars['menuItem'] = $menuItem;

    $properties = array(
      'body' => '<?php echo get_sympal_breadcrumbs($menuItem, $content) ?><h2><?php echo get_sympal_column_content_slot($content, \'title\') ?></h2><p><strong>Posted by <?php echo $content->CreatedBy->username ?> on <?php echo get_sympal_column_content_slot($content, \'date_published\') ?></strong></p><p><?php echo get_sympal_column_content_slot($content, \'body\') ?></p>',
    );

    $contentTemplate = $this->newContentTemplate('View '.$this->_contentTypeName, $contentType, $properties);
    $installVars['contentTemplate'] = $contentTemplate;

    return $installVars;
  }

  protected function _defaultInstallation($installVars)
  {
    $this->saveMenuItem($installVars['menuItem']);

    $installVars['contentType']->save();
    $installVars['contentTemplate']->save();
    $installVars['content']->save();
  }
}