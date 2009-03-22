<?php

class sfSympalPluginManagerInstall extends sfSympalPluginManager
{
  public $configuration;

  public function install($name, $entityTypeName = null)
  {
    if (is_null($entityTypeName))
    {
      $entityTypeName = $this->getEntityTypeForPlugin($name);
    }

    $pluginName = sfSympalTools::getLongPluginName($name);
    $name = sfSympalTools::getShortPluginName($name);

    $uninstall = new sfSympalPluginManagerUninstall();
    $uninstall->uninstall($pluginName);

    $path = $this->configuration->getPluginConfiguration($pluginName)->getRootDir();
    $schema = $path.'/config/doctrine/schema.yml';
    $pluginConfig = $this->configuration->getPluginConfiguration($pluginName);

    $installVars = array();

    if (file_exists($schema))
    {
      $dataFixtures = sfFinder::type('file')->in($path.'/data/fixtures/install.yml');
      $models = array_keys(sfYaml::load($schema));

      if ($ret = $this->_generateFilesFromSchema($name, $entityTypeName))
      {
        return $ret;
      }

      $this->logSection('sympal', 'Create the tables for the models');

      Doctrine::createTablesFromArray($models);

      if ($entityTypeName)
      {
        $installVars = $this->_createDefaultEntityTypeRecords($name, $entityTypeName, $installVars);
      }
    }

    if (method_exists($pluginConfig, 'install'))
    {
      $this->logSection('sympal', 'Calling '.$pluginName.'Configuration::install()');

      $this->configuration->getPluginConfiguration($pluginName)->install($installVars, $this);
    } else {
      if (isset($entityTypeName) && $entityTypeName)
      {
        $this->_defaultInstallation($installVars, $entityTypeName);
      }
      $createInstall = true;
    }

    if (isset($createInstall) && $createInstall)
    {
      $this->logSection('sympal', 'On the '.$pluginName.'Configuration class you can define a install() method to perform additional installation operaitons for your sympal plugin!');
    }
  }

  protected function _createDefaultEntityTypeRecords($name, $entityTypeName, $installVars)
  {
    $this->logSection('sympal', 'Create default entity type records');

    $lowerName = str_replace('-', '_', Doctrine_Inflector::urlize($name));
    $slug = 'sample-'.$lowerName;

    $entityType = new EntityType();
    $entityType->name = $entityTypeName;
    $entityType->label = $entityTypeName;
    $entityType->list_route_url = "/$lowerName/list";
    $entityType->view_route_url = "/$lowerName/:slug";
    $entityType->slug = $lowerName;
    $installVars['entityType'] = $entityType;

    $entity = new Entity();
    $entity->Type = $entityType;
    $entity->slug = $slug;
    $entity->is_published = true;
    $entity->CreatedBy = Doctrine::getTable('sfGuardUser')->findOneByUsername('admin');
    $entity->Site = Doctrine::getTable('Site')->findOneBySlug(sfConfig::get('sf_app'));
    $installVars['entity'] = $entity;

    $menuItem = new MenuItem();
    $menuItem->name = $name;
    $menuItem->is_published = true;
    $menuItem->label = $name;
    $menuItem->has_many_entities = true;
    $menuItem->EntityType = $entityType;
    $menuItem->Site = Doctrine::getTable('Site')->findOneBySlug(sfConfig::get('sf_app'));
    $installVars['menuItem'] = $menuItem;

    $entityTemplate = new EntityTemplate();
    $entityTemplate->name = 'View '.$entityTypeName;
    $entityTemplate->type = 'View';
    $entityTemplate->EntityType = $installVars['entityType'];
    $entityTemplate->body = '<?php echo get_sympal_breadcrumbs($menuItem, $entity) ?><h2><?php echo $entity->getHeaderTitle() ?></h2><p><strong>Posted by <?php echo $entity->CreatedBy->username ?> on <?php echo date(\'m/d/Y h:i:s\', strtotime($entity->created_at)) ?></strong></p><p><?php echo $entity->getRecord()->getBody() ?></p><?php echo get_sympal_comments($entity) ?>';
    $installVars['entityTemplate'] = $entityTemplate;

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

  protected function _defaultInstallation($installVars, $entityTypeName)
  {
    $this->logSection('sympal', 'No install() method found so running default installation');

    $this->addToMenu($installVars['menuItem']);

    $installVars['entity']->save();
    $installVars['entityType']->save();
    $installVars['entityTemplate']->save();

    $entityTypeRecord = new $entityTypeName();
    $entityTypeRecord->Entity = $installVars['entity'];

    $guesses = array('name',
                     'title',
                     'username',
                     'subject',
                     'body');

    try {
      foreach ($guesses as $guess)
      {
        $entityTypeRecord->$guess = 'Sample '.$entityTypeName;
      }
    } catch (Exception $e) {}

    if ($entityTypeRecord->getTable()->hasColumn('body'))
    {
      $entityTypeRecord->body = 'This is some sample content for the body your new entity type.';
    }
    $entityTypeRecord->save();
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