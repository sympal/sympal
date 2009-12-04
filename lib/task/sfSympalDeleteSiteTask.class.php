<?php

class sfSympalDeleteSiteTask extends sfTaskExtraBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application to delete'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', sfSympalToolkit::getDefaultApplication()),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('delete-app', null, sfCommandOption::PARAMETER_NONE, 'Delete Symfony application directory as well.'),
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'delete-site';
    $this->briefDescription = 'Delete a Sympal site';

    $this->detailedDescription = <<<EOF
The [sympal:delete-site|INFO] task will delete a Sympal site from the database
and remove the Symfony application as well

  [./sympal:delete-site my_site|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    if (!$options['no-confirmation'] && !$this->askConfirmation(array(sprintf('You are about to delete the site named %s', $arguments['application']), 'Are you sure you want to proceed? (y/N)'), null, false))
    {
      $this->logSection('sympal', 'Delete site task aborted');

      return 1;
    }

    $databaseManager = new sfDatabaseManager($this->configuration);

    $site = Doctrine_Core::getTable('Site')->findOneBySlug($arguments['application']);
    if ($site) {
      $this->logSection('sympal', sprintf('Deleting site named "%s"', $site->title));

      $site->delete();
      if (isset($options['delete-app']) && $options['delete-app'])
      {
        $site->deleteApplication();
      }
    } else {
      throw new InvalidArgumentException(sprintf('Could not find site "%s"', $arguments['application']));
    }
  }
}