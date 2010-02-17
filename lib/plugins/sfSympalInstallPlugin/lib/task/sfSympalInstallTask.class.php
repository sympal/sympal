<?php

class sfSympalInstallTask extends sfSympalBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::OPTIONAL, 'The application to install Sympal in.', sfSympalToolkit::getDefaultApplication()),
    ));

    $this->addOptions(array(
      new sfCommandOption('email-address', null, sfCommandOption::PARAMETER_OPTIONAL, 'The e-mail address of the first user to create', 'admin@sympalphp.org'),
      new sfCommandOption('username', null, sfCommandOption::PARAMETER_OPTIONAL, 'The username of the first user to create.', 'admin'),
      new sfCommandOption('password', null, sfCommandOption::PARAMETER_OPTIONAL, 'The password of the first user to create.', 'admin'),
      new sfCommandOption('first-name', null, sfCommandOption::PARAMETER_OPTIONAL, 'The first name of the first user to create.'),
      new sfCommandOption('last-name', null, sfCommandOption::PARAMETER_OPTIONAL, 'The last name of the first user to create.'),
      new sfCommandOption('db-dsn', null, sfCommandOption::PARAMETER_OPTIONAL, 'The database DSN to install with.'),
      new sfCommandOption('db-username', null, sfCommandOption::PARAMETER_OPTIONAL, 'The database username.'),
      new sfCommandOption('db-password', null, sfCommandOption::PARAMETER_OPTIONAL, 'The database password.'),
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
      new sfCommandOption('force-reinstall', null, sfCommandOption::PARAMETER_NONE, 'Force re-installation'),
      new sfCommandOption('build-classes', null, sfCommandOption::PARAMETER_OPTIONAL, 'Build all classes', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'install';
    $this->briefDescription = 'Install the Sympal CMS into a blank Symfony project';

    $this->detailedDescription = <<<EOF
The [./symfony sympal:install|INFO] task installs the Sympal CMS into a blank Symfony project:

  [./symfony sympal:install|INFO]

By default the task will find the first application in the apps folder and install 
Sympal for that application. You can specify the application with the first argument.

  [./symfony sympal:install my_app|INFO]

To force a full reinstall of sympal use the force-reinstall option:

  [./symfony sympal:install --force-reinstall|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    $install = new sfSympalInstall($this->configuration, $this->dispatcher, $this->formatter);
    $install->setApplication($arguments['application']);

    $msg = array();
    if (isset($options['force-reinstall']) && $options['force-reinstall']) {
      $msg[] = 'This command will remove all data in your configured databases and initialize the Sympal database.';
      $type = 'ERROR_LARGE';
    } else if ($install->checkSympalSiteExists()) {
      $msg[] = sprintf('Sympal has already been installed for the application named "%s".', $arguments['application']);
      $msg[] = 'Do you wish to delete the site and re-create it?';
      $msg[] = '';
      $msg[] = 'Use the --force-reinstall option to reinstall Sympal completely.';
      $msg[] = '';
      $type = 'ERROR_LARGE';
    } else if ($install->checkSympalDatabaseExists() && !$install->checkSympalSiteExists()) {
      $msg[] = sprintf('Sympal has already been installed but the application named "%s" has not had a site created for it yet.', $arguments['application']);
      $msg[] = 'Do you want to create a site record for this application?';
      $msg[] = '';
      $msg[] = 'Use the --force-reinstall option to reinstall Sympal completely.';
      $msg[] = '';
      $type = 'QUESTION_LARGE';
    } else {
      $msg[] = sprintf('This command will initialize the sympal Database for the application named "%s".', $arguments['application']);
      $type = 'QUESTION_LARGE';
    }

    $msg[] = 'Are you sure you want to proceed? (y/N)';

    if (!$options['no-confirmation'] && !$this->askConfirmation($msg, $type, false))
    {
      $this->logSection('sympal', 'Install task aborted');

      return 1;
    }

    $install->setParam('email_address', $options['email-address']);
    $install->setParam('username', $options['username']);
    $install->setParam('password', $options['password']);
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
    if (isset($options['force-reinstall']) && $options['force-reinstall'])
    {
      $install->setOption('force_reinstall', true);
    }
    $install->setOption('build_classes', (bool) $options['build-classes']);
    $install->install();

    $this->log(null);
    $this->logSection('sympal', sprintf('Sympal was installed successfully...', $arguments['application']), null, 'COMMENT');

    $url = 'http://localhost/'.$arguments['application'].'_dev.php/security/signin';
    $this->logSection('sympal', sprintf('Open your browser to "%s"', $url), null, 'COMMENT');
    $this->logSection('sympal', sprintf('You can signin with the username "%s" and password "%s"', $install->getParam('username'), $install->getParam('password')), null, 'COMMENT');
  }
}