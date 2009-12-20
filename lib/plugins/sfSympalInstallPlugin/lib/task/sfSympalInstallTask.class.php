<?php

class sfSympalInstallTask extends sfTaskExtraBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('email-address', sfCommandArgument::OPTIONAL, 'The e-mail address of the first user to create.', 'admin@sympalphp.org'),
      new sfCommandArgument('username', sfCommandArgument::OPTIONAL, 'The username of the first user to create.', 'admin'),
      new sfCommandArgument('password', sfCommandArgument::OPTIONAL, 'The password of the first user to create.', 'admin'),
    ));

    $this->addOptions(array(
      new sfCommandOption('first-name', null, sfCommandOption::PARAMETER_OPTIONAL, 'The first name of the first user to create.'),
      new sfCommandOption('last-name', null, sfCommandOption::PARAMETER_OPTIONAL, 'The last name of the first user to create.'),
      new sfCommandOption('db-dsn', null, sfCommandOption::PARAMETER_OPTIONAL, 'The database DSN to install with.'),
      new sfCommandOption('db-username', null, sfCommandOption::PARAMETER_OPTIONAL, 'The database username.'),
      new sfCommandOption('db-password', null, sfCommandOption::PARAMETER_OPTIONAL, 'The database password.'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The title of the Sympal site to install', sfSympalToolkit::getDefaultApplication()),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'install';
    $this->briefDescription = 'Install the Sympal CMS into a blank Symfony project';

    $this->detailedDescription = <<<EOF
The [./symfony sympal:install|INFO] task installs the Sympal CMS into a blank Symfony project:

  [./symfony sympal:install|INFO]

By default the task will find the first application in the apps folder and install 
Sympal for that application. You can specify the application with the --application
option:

  [./symfony sympal:install --application=my_app|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    if (!$options['no-confirmation'] && !$this->askConfirmation(array('This command will remove all data in your configured databases and initialize the sympal database.', 'Are you sure you want to proceed? (y/N)'), 'QUESTION_LARGE', false))
    {
      $this->logSection('sympal', 'Install task aborted');

      return 1;
    }

    $databaseManager = new sfDatabaseManager($this->configuration);

    $install = new sfSympalInstall($this->configuration, $this->dispatcher, $this->formatter);
    $install->setApplication($options['application']);
    $install->setParam('email_address', $arguments['email-address']);
    $install->setParam('username', $arguments['username']);
    $install->setParam('password', $arguments['password']);
    if (isset($options['first-name']))
    {
      $install->setParam('first_name', $options['first-name']);
    }
    if (isset($options['last-name']))
    {
      $install->setParam('last_name', $options['last-name']);
    }
    if (isset($options['db-dsn']))
    {
      $install->setParam('db_dsn', $options['db-dsn']);
    }
    if (isset($options['db-username']))
    {
      $install->setParam('db_username', $options['db-username']);
    }
    if (isset($options['db-password']))
    {
      $install->setParam('db_password', $options['db-password']);
    }

    $install->install();

    $this->log(null);
    $this->logSection('sympal', sprintf('Sympal was installed successfully...', $options['application']), null, 'COMMENT');

    $url = 'http://localhost/'.$options['application'].'_dev.php/security/signin';
    $this->logSection('sympal', sprintf('Open your browser to "%s"', $url), null, 'COMMENT');
    $this->logSection('sympal', sprintf('You can signin with the username "%s" and password "%s"', $install->getParam('username'), $install->getParam('password')), null, 'COMMENT');
  }
}