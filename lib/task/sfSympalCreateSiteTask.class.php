<?php

class sfSympalCreateSiteTask extends sfTaskExtraBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The site/application title'),
    ));

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('layout', null, sfCommandOption::PARAMETER_OPTIONAL, 'The site/application layout', null),
      new sfCommandOption('interactive', null, sfCommandOption::PARAMETER_NONE, 'Interactive installation option'),
      new sfCommandOption('load-dummy-data', null, sfCommandOption::PARAMETER_NONE, 'Load dummy data for the newly created site.'),
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
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

  protected function _getOrCreateSite($arguments, $options)
  {
    $site = Doctrine_Core::getTable('Site')->findOneBySlug($arguments['application']);
    if (!$site)
    {
      $this->logSection('sympal', 'Creating new site record in database...');
      $site = new Site();
      $site->title = $arguments['application'];
      $site->slug = $arguments['application'];
    }

    if (!$site->description)
    {
      $site->description = 'Description for '.$arguments['application'].' site.';
    }

    if ($options['layout'])
    {
      $site->layout = $options['layout'];
    }

    $site->save();
    
    return $site;
  }

  protected function _prepareApplication(Site $site)
  {
    $task = new sfSympalPrepareApplicationTask($this->dispatcher, $this->formatter);
    $task->run(array($site->slug), array());
  }

  protected function _installSiteData()
  {
  }

  protected function execute($arguments = array(), $options = array())
  {
    if (!$options['no-confirmation'] && !$this->askConfirmation(array(sprintf('You are about to create a new site named %s', $arguments['application']), 'Are you sure you want to proceed? (y/N)'), 'QUESTION_LARGE', false))
    {
      $this->logSection('sympal', 'Install task aborted');

      return 1;
    }

    $path = sfConfig::get('sf_apps_dir').'/'.$arguments['application'];
    if (!file_exists($path))
    {
      throw new sfException(sprintf('Could not find a Symfony application named "%s". You must generate an application with the generate:app task.', $options['application']));
    }

    if (isset($options['interactive']) && $options['interactive'])
    {
      sfSympalConfig::set('sympal_install_admin_email_address', $this->askAndValidate('Enter E-Mail Address:', new sfValidatorString()));
      sfSympalConfig::set('sympal_install_admin_first_name', $this->askAndValidate('Enter First Name:', new sfValidatorString()));
      sfSympalConfig::set('sympal_install_admin_last_name', $this->askAndValidate('Enter Last Name:', new sfValidatorString()));
      sfSympalConfig::set('sympal_install_admin_username', $this->askAndValidate('Enter Username:', new sfValidatorString()));
      sfSympalConfig::set('sympal_install_admin_password', $this->askAndValidate('Enter Password:', new sfValidatorString()));
    }

    $databaseManager = new sfDatabaseManager($this->configuration);
    $site = $this->_getOrCreateSite($arguments, $options);
    $this->_prepareApplication($site);
    $this->_installSiteData($site);
  }
}