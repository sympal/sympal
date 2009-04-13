<?php

class sfSympalCreateSiteTask extends sfTaskExtraBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('title', sfCommandArgument::REQUIRED, 'The site/application title'),
      new sfCommandArgument('description', sfCommandArgument::OPTIONAL, 'The site/application description'),
    ));

    $this->addOptions(array(
      new sfCommandOption('layout', null, sfCommandOption::PARAMETER_OPTIONAL, 'The site/application layout', null),
      new sfCommandOption('escaping-strategy', null, sfCommandOption::PARAMETER_REQUIRED, 'Output escaping strategy', false),
      new sfCommandOption('csrf-secret', null, sfCommandOption::PARAMETER_REQUIRED, 'Secret to use for CSRF protection', false),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', sfSympalToolkit::getFirstApplication()),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'create-site';
    $this->briefDescription = 'Install the sympal plugin content management framework.';

    $this->detailedDescription = <<<EOF
The [sympal:create-site|INFO] task will create a new Sympal site in the database
and generate the according symfony application.

  [./sympal:create-site|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    $site = new Site();
    $site->title = $arguments['title'];
    $site->description = $arguments['description'] ? $arguments['description']:'Description for new site named '.$arguments['title'];
    $site->layout = $options['layout'] ? $options['layout']:'layout';
    $site->slug = str_replace('-', '_', Doctrine_Inflector::urlize($arguments['title']));
    $site->save();

    $homeContent = Content::createNew('Page');
    $homeContent->slug = 'home';
    $homeContent->Site = $site;
    $homeContent->is_published = true;
    $homeContent->title = 'New Site Home';
    $homeContent->custom_path = '/';
    $homeContent->CreatedBy = Doctrine::getTable('User')->findOneByIsSuperAdmin(true);
    $homeContent->save();

    $testContent = Content::createNew('Page');
    $testContent->slug = 'test-page';
    $testContent->Site = $site;
    $testContent->is_published = true;
    $testContent->title = 'Test Page';
    $testContent->CreatedBy = Doctrine::getTable('User')->findOneByIsSuperAdmin(true);
    $testContent->save();

    $tree = Doctrine::getTable('MenuItem')->getTree();
    $root = new MenuItem();
    $root->name = 'primary';
    $root->label = 'New Site';
    $root->is_published = true;
    $root->is_primary = true;
    $root->Site = $site;
    $root->RelatedContent = $homeContent;
    $root->save();
    $tree->createRoot($root);

    $menuItem = new MenuItem();
    $menuItem->name = 'Test Page';
    $menuItem->is_published = true;
    $menuItem->Site = $site;
    $menuItem->RelatedContent = $testContent;
    $menuItem->getNode()->insertAsLastChildOf($root);

    $menuItem = new MenuItem();
    $menuItem->name = 'Home';
    $menuItem->is_published = true;
    $menuItem->Site = $site;
    $menuItem->RelatedContent = $homeContent;
    $menuItem->getNode()->insertAsLastChildOf($root);

    $task = new sfGenerateAppTask($this->dispatcher, $this->formatter);
    $task->setCommandApplication($this->commandApplication);

    $taskOptions = array();
    if (isset($options['escaping-strategy']) && $options['escaping-strategy'])
    {
      $taskOptions[] = '--escaping-strategy='.$options['escaping-strategy'];
    }
    if (isset($options['csrf-secret']) && $options['csrf-secret'])
    {
      $taskOptions[] = '--csrf-secret='.$options['csrf-secret'];
    }

    $task->run(array($site->slug), $taskOptions);

    file_put_contents(sfConfig::get('sf_root_dir').'/apps/'.$site->slug.'/config/routing.yml', '');

    $userPath = sfConfig::get('sf_root_dir').'/apps/'.$site->slug.'/lib/myUser.class.php';
    file_put_contents($userPath, str_replace('extends sfBasicSecurityUser', 'extends sfSympalUser', file_get_contents($userPath)));

    $yml = 'all:
  sympal_config:
    default_layout: '.$site->layout;

    file_put_contents(sfConfig::get('sf_root_dir').'/apps/'.$site->slug.'/config/app.yml', $yml);

    $layout = file_get_contents(realpath(dirname(__FILE__).'/../..').'/data/default_site_layout.php');
    $path = sfConfig::get('sf_root_dir').'/apps/'.$site->slug.'/templates/layout.php';
    file_put_contents($path, $layout);
  }
}