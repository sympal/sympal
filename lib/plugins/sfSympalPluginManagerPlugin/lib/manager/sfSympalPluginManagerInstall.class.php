<?php

class sfSympalPluginManagerInstall extends sfSympalPluginManager
{
  public $configuration;

  public function install()
  {
    $this->_disableProdApplication();

    $uninstall = new sfSympalPluginManagerUninstall($this->_pluginName, $this->_configuration, $this->_formatter);
    $uninstall->uninstall();

    try {
      $path = $this->_configuration->getPluginConfiguration($this->_pluginName)->getRootDir();
      $schema = $path.'/config/doctrine/schema.yml';
      $pluginConfig = $this->_configuration->getPluginConfiguration($this->_pluginName);

      $installVars = array();
      if (file_exists($schema))
      {
        $dataFixtures = sfFinder::type('file')->in($path.'/data/fixtures/install.yml');
        $models = array_keys(sfYaml::load($schema));

        $this->_generateFilesFromSchema();

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
        $createInstall = true;
      }

      if (isset($createInstall) && $createInstall)
      {
        $this->logSection('sympal', 'On the '.$this->_pluginName.'Configuration class you can define a install() method to perform additional installation operaitons for your sympal plugin!');
      }

      chdir(sfConfig::get('sf_root_dir'));
      $assets = new sfPluginPublishAssetsTask($this->_dispatcher, $this->_formatter);
      $ret = @$assets->run(array(), array());

      $this->_enableProdApplication();

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
    $slug = 'sample-'.$lowerName;

    $properties = array(
      'label' => $this->_contentTypeName,
      'list_path' => "/$lowerName/list",
      'view_path' => "/$lowerName/:slug",
      'slug' => $lowerName,
      'plugin_name' => $this->_pluginName,
    );

    $contentType = $this->newContentType($this->_contentTypeName, $properties);
    $installVars['contentType'] = $contentType;

    $properties = array(
      'Type' => $contentType,
      'slug' => $slug,
      'is_published' => true,
      'CreatedBy' => Doctrine::getTable('sfGuardUser')->findOneByUsername('admin'),
      'Site' => Doctrine::getTable('Site')->findOneBySlug(sfConfig::get('sf_app')),
    );

    $content = $this->newContent($contentType, $properties);
    $installVars['content'] = $content;

    $properties = array(
      'is_published' => true,
      'label' => $this->_name,
      'is_content_type_list' => true,
      'ContentType' => $contentType,
      'Site' => Doctrine::getTable('Site')->findOneBySlug(sfConfig::get('sf_app'))
    );

    $menuItem = $this->newMenuItem($this->_name, $properties);
    $installVars['menuItem'] = $menuItem;

    $properties = array(
      'body' => '<?php echo get_sympal_breadcrumbs($menuItem, $content) ?><h2><?php echo $content->getHeaderTitle() ?></h2><p><strong>Posted by <?php echo $content->CreatedBy->username ?> on <?php echo date(\'m/d/Y h:i:s\', strtotime($content->created_at)) ?></strong></p><p><?php echo $content->getRecord()->getBody() ?></p><?php echo get_sympal_comments($content) ?>',
    );

    $contentTemplate = $this->newContentTemplate('View '.$this->_contentTypeName, 'View', $installVars['contentType'], $properties);
    $installVars['contentTemplate'] = $contentTemplate;

    return $installVars;
  }

  protected function _defaultInstallation($installVars)
  {
    $this->logSection('sympal', 'No customInstall() method found so running default installation');

    $this->saveMenuItem($installVars['menuItem']);

    $contentTypeName = $this->_contentTypeName;
    $contentTypeRecord = $installVars['content']->$contentTypeName;

    $guesses = array('name',
                     'title',
                     'username',
                     'subject',
                     'body');

    try {
      foreach ($guesses as $guess)
      {
        $contentTypeRecord->$guess = 'Sample '.$this->_contentTypeName;
      }
    } catch (Exception $e) {}

    if ($contentTypeRecord->getTable()->hasColumn('body'))
    {
      $contentTypeRecord->body = 'This is some sample content for the body your new content type.';
    }

    $installVars['contentType']->save();
    $installVars['contentTemplate']->save();
    $installVars['content']->save();
  }

  protected function _generateFilesFromSchema()
  {
    $this->logSection('sympal', 'Generate new forms, filters and models.');

    $baseOptions = $this->_configuration instanceof sfApplicationConfiguration ? array(
      '--application='.sfConfig::get('sf_app'),
      '--env='.sfConfig::get('sf_env', 'dev'),
    ) : array();

    $cwd = getcwd();
    chdir(sfConfig::get('sf_root_dir'));

    $buildModel = new sfDoctrineBuildModelTask($this->_dispatcher, $this->_formatter);
    $ret = $buildModel->run(array(), $baseOptions);

    if ($ret)
    {
      return $ret;
    }

    $buildSql = new sfDoctrineBuildSqlTask($this->_dispatcher, $this->_formatter);
    $ret = $buildSql->run(array(), $baseOptions);

    if ($ret)
    {
      return $ret;
    }

    $buildForms = new sfDoctrineBuildFormsTask($this->_dispatcher, $this->_formatter);
    $ret = $buildForms->run(array(), $baseOptions);

    if ($ret)
    {
      return $ret;
    }

    $buildFilters = new sfDoctrineBuildFiltersTask($this->_dispatcher, $this->_formatter);
    $ret = $buildFilters->run(array(), $baseOptions);

    if ($ret)
    {
      return $ret;
    }

    $cc = new sfCacheClearTask($this->_dispatcher, $this->_formatter);
    $ret = $cc->run(array(), array());
    chdir($cwd);
  }
}