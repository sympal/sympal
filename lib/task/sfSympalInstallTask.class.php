<?php

class sfSympalInstallTask extends sfTaskExtraBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('interactive', null, sfCommandOption::PARAMETER_NONE, 'Interactive installation option'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', sfSympalToolkit::getFirstApplication()),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
      new sfCommandOption('skip-forms', 'F', sfCommandOption::PARAMETER_NONE, 'Skip generating forms'),
      new sfCommandOption('dir', null, sfCommandOption::PARAMETER_REQUIRED | sfCommandOption::IS_ARRAY, 'The directories to look for fixtures'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'install';
    $this->briefDescription = 'Install the sympal plugin content management framework.';

    $this->detailedDescription = <<<EOF
The [sympal:install|INFO] task is a shortcut for five other tasks:

  [./sympal:install|INFO]

The task is equivalent to:

  [./symfony doctrine:drop-db|INFO]
  [./symfony doctrine:build-db|INFO]
  [./symfony doctrine:build-model|INFO]
  [./symfony doctrine:insert-sql|INFO]
  [./symfony doctrine:data-load|INFO]
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
      sfSympalConfig::set('sympal_install_admin_email_address', $this->askAndValidate('Enter E-Mail Address:', new sfValidatorString()));
      sfSympalConfig::set('sympal_install_admin_first_name', $this->askAndValidate('Enter First Name:', new sfValidatorString()));
      sfSympalConfig::set('sympal_install_admin_last_name', $this->askAndValidate('Enter Last Name:', new sfValidatorString()));
      sfSympalConfig::set('sympal_install_admin_username', $this->askAndValidate('Enter Username:', new sfValidatorString()));
      sfSympalConfig::set('sympal_install_admin_password', $this->askAndValidate('Enter Password:', new sfValidatorString()));
    }

    $install = new sfSympalInstall($this->configuration, $this->dispatcher, $this->formatter);
    $install->install();
  }
}