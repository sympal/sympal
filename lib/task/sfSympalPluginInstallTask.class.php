<?php

class sfSympalPluginInstallTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'The name of the functionality. i.e. sfSympal#NAME#Plugin'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'sympal'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'plugin-install';
    $this->briefDescription = 'Install a sympal plugin to an existing sympal installation';

    $this->detailedDescription = <<<EOF
The [sympal:plugin-install|INFO] is a task to install a plugin to an existing sympal installation.
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    $name = $arguments['name'];
    $pluginName = 'sfSympal'.Doctrine_Inflector::classify($name).'Plugin';
    $path = $this->configuration->getPluginConfiguration($pluginName)->getRootDir();
    $schema = $path.'/config/doctrine/schema.yml';
    $dataFixtures = sfFinder::type('file')->in($path.'/data/fixtures/install.yml');
    $models = array_keys(sfYaml::load($schema));
    $entityTypeName = current($models);

    $baseOptions = $this->configuration instanceof sfApplicationConfiguration ? array(
      '--application='.$this->configuration->getApplication(),
      '--env='.$options['env'],
    ) : array();

    $this->logSection('sympal', 'Generate new forms, filters and models.');

    $buildModel = new sfDoctrineBuildModelTask($this->dispatcher, $this->formatter);
    $buildModel->setCommandApplication($this->commandApplication);
    $ret = $buildModel->run(array(), $baseOptions);

    if ($ret)
    {
      return $ret;
    }

    $buildSql = new sfDoctrineBuildSqlTask($this->dispatcher, $this->formatter);
    $buildSql->setCommandApplication($this->commandApplication);
    $ret = $buildSql->run(array(), $baseOptions);

    if ($ret)
    {
      return $ret;
    }

    $buildForms = new sfDoctrineBuildFormsTask($this->dispatcher, $this->formatter);
    $buildForms->setCommandApplication($this->commandApplication);
    $ret = $buildForms->run(array(), $baseOptions);

    if ($ret)
    {
      return $ret;
    }

    $buildFilters = new sfDoctrineBuildFiltersTask($this->dispatcher, $this->formatter);
    $buildFilters->setCommandApplication($this->commandApplication);
    $ret = $buildFilters->run(array(), $baseOptions);

    if ($ret)
    {
      return $ret;
    }

    $cc = new sfCacheClearTask($this->dispatcher, $this->formatter);
    $cc->setCommandApplication($this->commandApplication);
    $ret = $cc->run(array(), array());

    $this->logSection('sympal', 'Create the tables for the entity');

    Doctrine::createTablesFromArray(array($entityTypeName));

    $lowerName = str_replace('-', '_', Doctrine_Inflector::urlize($name));
    $slug = 'sample-'.$lowerName;

    $this->logSection('sympal', 'Install the plugin to the database');

    $entityType = new EntityType();
    $entityType->name = $entityTypeName;
    $entityType->label = $entityTypeName;
    $entityType->list_route_url = "/$lowerName/list";
    $entityType->view_route_url = "/$lowerName/:slug";
    $entityType->slug = $lowerName;
    $entityType->save();

    $entityTemplate = new EntityTemplate();
    $entityTemplate->name = 'View '.$entityTypeName;
    $entityTemplate->type = 'View';
    $entityTemplate->EntityType = $entityType;
    $entityTemplate->body = '<h1><?php echo $entity->getHeaderTitle() ?></h1><p><?php echo $entity->getRecord()->getBody() ?></p>';
    $entityTemplate->save();

    $entity = new Entity();
    $entity->Type = $entityType;
    $entity->slug = $slug;
    $entity->is_published = true;
    $entity->CreatedBy = Doctrine::getTable('sfGuardUser')->findOneByUsername('admin');
    $entity->Site = Doctrine::getTable('Site')->findOneBySlug($options['application']);
    $entity->save();

    $entityTypeRecord = new $entityTypeName();
    $entityTypeRecord->Entity = $entity;

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

    $roots = Doctrine::getTable('MenuItem')->getTree()->fetchRoots();
    $root = $roots[0];

    $menuItem = new MenuItem();
    $menuItem->name = $name;
    $menuItem->is_published = true;
    $menuItem->label = $name;
    $menuItem->has_many_entities = true;
    $menuItem->EntityType = $entityType;
    $menuItem->Site = Doctrine::getTable('Site')->findOneBySlug($options['application']);
    $menuItem->getNode()->insertAsLastChildOf($root);
  }
}