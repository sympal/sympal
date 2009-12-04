<?php

class sfSympalInstallTask extends sfTaskExtraBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::OPTIONAL, 'The title of the Sympal site to install', sfSympalToolkit::getDefaultApplication()),
    ));

    $this->addOptions(array(
      new sfCommandOption('interactive', null, sfCommandOption::PARAMETER_NONE, 'Interactive installation option'),
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'install';
    $this->briefDescription = 'Install the Sympal CMS into a blank Symfony project';

    $this->detailedDescription = <<<EOF
The [sympal:install|INFO] task installs the Sympal CMS into a blank Symfony project:

  [./sympal:install|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    if (!$options['no-confirmation'] && !$this->askConfirmation(array('This command will remove all data in your configured databases and initialize the sympal database.', 'Are you sure you want to proceed? (y/N)'), null, false))
    {
      $this->logSection('sympal', 'Install task aborted');

      return 1;
    }

    if (isset($options['interactive']) && $options['interactive'])
    {
      sfSympalConfig::set('sympal_install_database_dsn', $this->askAndValidate('Enter Database DSN:', new sfValidatorString()));
      sfSympalConfig::set('sympal_install_database_username', $this->askAndValidate('Enter Database Username:', new sfValidatorString()));
      sfSympalConfig::set('sympal_install_database_password', $this->askAndValidate('Enter Database Password:', new sfValidatorString(array('required' => false))));
    }

    $databaseManager = new sfDatabaseManager($this->configuration);

    $install = new sfSympalInstall($this->configuration, $this->dispatcher, $this->formatter);
    $install->setApplication($arguments['application']);
    $install->install();

    $this->log(null);
    $this->logSection('sympal', sprintf('Sympal was installed successfully...', $arguments['application']));

    $url = 'http://localhost/'.sfConfig::get('sf_app').'_dev.php';
    $this->logSection('sympal', sprintf('Open your browser to "%s"', $url));
  }
}