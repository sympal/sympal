<?php

class sfSympalCreateSiteTask extends sfSympalBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('site', sfCommandArgument::REQUIRED, 'The site'),
    ));

    $this->addOptions(array(
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

  protected function execute($arguments = array(), $options = array())
  {
    if (!$options['no-confirmation'] && !$this->askConfirmation(array(sprintf('You are about to create a new site named "%s"', $arguments['site']), 'Are you sure you want to proceed? (y/N)'), 'QUESTION_LARGE', false))
    {
      $this->logSection('sympal', 'Install task aborted');

      return 1;
    }

    if (isset($options['interactive']) && $options['interactive'])
    {
      sfSympalConfig::set('sympal_install_admin_email_address', $this->askAndValidate('Enter E-Mail Address:', new sfValidatorString()));
      sfSympalConfig::set('sympal_install_admin_first_name', $this->askAndValidate('Enter First Name:', new sfValidatorString()));
      sfSympalConfig::set('sympal_install_admin_last_name', $this->askAndValidate('Enter Last Name:', new sfValidatorString()));
      sfSympalConfig::set('sympal_install_admin_username', $this->askAndValidate('Enter Username:', new sfValidatorString()));
      sfSympalConfig::set('sympal_install_admin_password', $this->askAndValidate('Enter Password:', new sfValidatorString()));
    }

    $this->_generateApplication($arguments['site']);
    $this->_prepareApplication($arguments['site']);

    $this->configuration = $this->createConfiguration($arguments['site'], 'dev');
    $context = sfContext::createInstance($this->configuration);

    $databaseManager = new sfDatabaseManager($this->configuration);
    $site = $this->_getOrCreateSite($arguments, $options);
  }

  protected function _getOrCreateSite($arguments, $options)
  {
    $site = Doctrine_Core::getTable('sfSympalSite')
      ->createQuery('s')
      ->where('s.slug = ?', $arguments['site'])
      ->fetchOne();
    if (!$site)
    {
      $this->logSection('sympal', 'Creating new site record in database...');
      $site = new sfSympalSite();
      $site->title = sfInflector::humanize($arguments['site']);
      $site->slug = $arguments['site'];
    }

    if (!$site->description)
    {
      $site->description = 'Description for '.$arguments['site'].' site.';
    }

    if ($options['layout'])
    {
      $site->layout = $options['layout'];
    }

    $site->save();
    
    return $site;
  }

  protected function _generateApplication($application)
  {
    try {
      $task = new sfGenerateAppTask($this->dispatcher, $this->formatter);
      $task->run(array($application), array());
    } catch (Exception $e) {}
  }

  protected function _prepareApplication($application)
  {
    $task = new sfSympalEnableForAppTask($this->dispatcher, $this->formatter);
    $task->run(array($application), array());
  }
}