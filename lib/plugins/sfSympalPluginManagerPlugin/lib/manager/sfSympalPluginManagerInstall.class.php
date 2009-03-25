<?php

class sfSympalPluginManagerInstall extends sfSympalPluginManager
{
  public $configuration;

  public function install($name, $contentTypeName = null)
  {
    if (is_null($contentTypeName))
    {
      $contentTypeName = $this->getContentTypeForPlugin($name);
    }

    $pluginName = sfSympalTools::getLongPluginName($name);
    $name = sfSympalTools::getShortPluginName($name);

    $uninstall = new sfSympalPluginManagerUninstall();
    $uninstall->uninstall($pluginName);

    sfSympalConfig::writeSetting($pluginName, 'installed', true);

    $path = $this->configuration->getPluginConfiguration($pluginName)->getRootDir();
    $schema = $path.'/config/doctrine/schema.yml';
    $pluginConfig = $this->configuration->getPluginConfiguration($pluginName);

    $installVars = array();

    if (file_exists($schema))
    {
      $dataFixtures = sfFinder::type('file')->in($path.'/data/fixtures/install.yml');
      $models = array_keys(sfYaml::load($schema));

      if ($ret = $this->_generateFilesFromSchema($name, $contentTypeName))
      {
        return $ret;
      }

      $this->logSection('sympal', 'Create the tables for the models');

      Doctrine::createTablesFromArray($models);

      if ($contentTypeName)
      {
        $installVars = $this->_createDefaultContentTypeRecords($name, $contentTypeName, $installVars);
      }
    }

    if (method_exists($pluginConfig, 'install'))
    {
      $this->logSection('sympal', 'Calling '.$pluginName.'Configuration::install()');

      $this->configuration->getPluginConfiguration($pluginName)->install($installVars, $this);
    } else {
      if (isset($contentTypeName) && $contentTypeName)
      {
        $this->_defaultInstallation($installVars, $contentTypeName);
      }
      $createInstall = true;
    }

    if (isset($createInstall) && $createInstall)
    {
      $this->logSection('sympal', 'On the '.$pluginName.'Configuration class you can define a install() method to perform additional installation operaitons for your sympal plugin!');
    }

    chdir(sfConfig::get('sf_root_dir'));
    $assets = new sfPluginPublishAssetsTask($this->dispatcher, $this->formatter);
    $ret = @$assets->run(array(), array());
  }

  protected function _createDefaultContentTypeRecords($name, $contentTypeName, $installVars)
  {
    $this->logSection('sympal', 'Create default content type records');

    $lowerName = str_replace('-', '_', Doctrine_Inflector::urlize($name));
    $slug = 'sample-'.$lowerName;

    $contentType = new ContentType();
    $contentType->name = $contentTypeName;
    $contentType->label = $contentTypeName;
    $contentType->list_route_url = "/$lowerName/list";
    $contentType->view_route_url = "/$lowerName/:slug";
    $contentType->slug = $lowerName;
    $installVars['contentType'] = $contentType;

    $content = new Content();
    $content->Type = $contentType;
    $content->slug = $slug;
    $content->is_published = true;
    $content->CreatedBy = Doctrine::getTable('sfGuardUser')->findOneByUsername('admin');
    $content->Site = Doctrine::getTable('Site')->findOneBySlug(sfConfig::get('sf_app'));
    $installVars['content'] = $content;

    $menuItem = new MenuItem();
    $menuItem->name = $name;
    $menuItem->is_published = true;
    $menuItem->label = $name;
    $menuItem->has_many_content = true;
    $menuItem->ContentType = $contentType;
    $menuItem->Site = Doctrine::getTable('Site')->findOneBySlug(sfConfig::get('sf_app'));
    $installVars['menuItem'] = $menuItem;

    $contentTemplate = new ContentTemplate();
    $contentTemplate->name = 'View '.$contentTypeName;
    $contentTemplate->type = 'View';
    $contentTemplate->ContentType = $installVars['contentType'];
    $contentTemplate->body = '<?php echo get_sympal_breadcrumbs($menuItem, $content) ?><h2><?php echo $content->getHeaderTitle() ?></h2><p><strong>Posted by <?php echo $content->CreatedBy->username ?> on <?php echo date(\'m/d/Y h:i:s\', strtotime($content->created_at)) ?></strong></p><p><?php echo $content->getRecord()->getBody() ?></p><?php echo get_sympal_comments($content) ?>';
    $installVars['contentTemplate'] = $contentTemplate;

    return $installVars;
  }

  public function addToMenu($menuItem)
  {
    $menuItem->is_published = true;
    $menuItem->Site = Doctrine::getTable('Site')->findOneBySlug(sfConfig::get('sf_app'));

    $roots = Doctrine::getTable('MenuItem')->getTree()->fetchRoots();
    $root = $roots[0];
    $menuItem->getNode()->insertAsLastChildOf($root);
  }

  protected function _defaultInstallation($installVars, $contentTypeName)
  {
    $this->logSection('sympal', 'No install() method found so running default installation');

    $this->addToMenu($installVars['menuItem']);

    $installVars['content']->save();
    $installVars['contentType']->save();
    $installVars['contentTemplate']->save();

    $contentTypeRecord = new $contentTypeName();
    $contentTypeRecord->Content = $installVars['content'];

    $guesses = array('name',
                     'title',
                     'username',
                     'subject',
                     'body');

    try {
      foreach ($guesses as $guess)
      {
        $contentTypeRecord->$guess = 'Sample '.$contentTypeName;
      }
    } catch (Exception $e) {}

    if ($contentTypeRecord->getTable()->hasColumn('body'))
    {
      $contentTypeRecord->body = 'This is some sample content for the body your new content type.';
    }
    $contentTypeRecord->save();
  }

  protected function _generateFilesFromSchema()
  {
    $this->logSection('sympal', 'Generate new forms, filters and models.');

    $baseOptions = $this->configuration instanceof sfApplicationConfiguration ? array(
      '--application='.sfConfig::get('sf_app'),
      '--env='.sfConfig::get('sf_env', 'dev'),
    ) : array();

    $cwd = getcwd();
    chdir(sfConfig::get('sf_root_dir'));

    $buildModel = new sfDoctrineBuildModelTask($this->dispatcher, $this->formatter);
    $ret = $buildModel->run(array(), $baseOptions);

    if ($ret)
    {
      return $ret;
    }

    $buildSql = new sfDoctrineBuildSqlTask($this->dispatcher, $this->formatter);
    $ret = $buildSql->run(array(), $baseOptions);

    if ($ret)
    {
      return $ret;
    }

    $buildForms = new sfDoctrineBuildFormsTask($this->dispatcher, $this->formatter);
    $ret = $buildForms->run(array(), $baseOptions);

    if ($ret)
    {
      return $ret;
    }

    $buildFilters = new sfDoctrineBuildFiltersTask($this->dispatcher, $this->formatter);
    $ret = $buildFilters->run(array(), $baseOptions);

    if ($ret)
    {
      return $ret;
    }

    $cc = new sfCacheClearTask($this->dispatcher, $this->formatter);
    $ret = $cc->run(array(), array());
    chdir($cwd);
  }
}