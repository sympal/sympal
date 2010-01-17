<?php

class sfSympalDeleteSiteTask extends sfSympalBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application to delete'),
    ));

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('and-app', null, sfCommandOption::PARAMETER_NONE, 'Delete Symfony application directory as well.'),
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'delete-site';
    $this->briefDescription = 'Delete a Sympal site';

    $this->detailedDescription = <<<EOF
The [sympal:delete-site|INFO] task will delete a Sympal site from the database
and remove the Symfony application as well

  [./symfony sympal:delete-site my_site|INFO]

You can delete the associated Symfony application as well:

  [./symfony sympal:delete-site my_site --and-app|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    if (!$options['no-confirmation'] && !$this->askConfirmation(array(sprintf('You are about to delete the site named "%s"', $arguments['application']), 'Are you sure you want to proceed? (y/N)'), 'QUESTION_LARGE', false))
    {
      $this->logSection('sympal', 'Delete site task aborted');

      return 1;
    }

    $databaseManager = new sfDatabaseManager($this->configuration);

    $site = Doctrine_Core::getTable('sfSympalSite')->findOneBySlug($arguments['application']);
    if ($site) {
      $this->logSection('sympal', sprintf('Deleting site named "%s" from database...', $site->title));

      $site->delete();
    }

    if (isset($options['and-app']) && $options['and-app'])
    {
      $this->logSection('sympal', sprintf('Deleting Symfony application named "%s"...', $arguments['application']));

      sfToolkit::clearDirectory(sfConfig::get('sf_apps_dir').'/'.$arguments['application']);
      rmdir(sfConfig::get('sf_apps_dir').'/'.$arguments['application']);
      @unlink(sfConfig::get('sf_web_dir').'/'.$arguments['application'].'_dev.php');
      @unlink(sfConfig::get('sf_web_dir').'/'.$arguments['application'].'.php');
    }
  }
}