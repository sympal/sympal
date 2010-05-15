<?php

/**
 * The base installation task for sympal. This:
 *   * configures i18n
 *   * creates a web/cache directory (if using minifier)
 *   * build all doctrine classes
 *   * builds all db tables
 *   * copies test fixtures (optional)
 *   * loads fixtures data
 * 
 * @package     sfSympalInstallPlugin
 * @subpackage  task
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */
class sfSympalInstallTask extends sfSympalBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::OPTIONAL, 'The application to install sympal in.', sfSympalToolkit::getDefaultApplication()),
    ));

    $this->addOptions(array(
      new sfCommandOption('email', null, sfCommandOption::PARAMETER_OPTIONAL, 'The e-mail address of the first user to create', 'admin@sympalphp.org'),
      new sfCommandOption('username', null, sfCommandOption::PARAMETER_OPTIONAL, 'The username of the first user to create.', 'admin'),
      new sfCommandOption('password', null, sfCommandOption::PARAMETER_OPTIONAL, 'The password of the first user to create.', 'admin'),
      new sfCommandOption('first-name', null, sfCommandOption::PARAMETER_OPTIONAL, 'The first name of the first user to create.'),
      new sfCommandOption('last-name', null, sfCommandOption::PARAMETER_OPTIONAL, 'The last name of the first user to create.'),
      
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'install';
    $this->briefDescription = 'Install the sympal CMF into a blank symfony project';

    $this->detailedDescription = <<<EOF
The [./symfony sympal:install|INFO] task installs the sympal CMF into a blank symfony project:

  [./symfony sympal:install|INFO]

By default the task will find the first application in the apps folder and install 
sympal for that application. You can specify the application with the first argument.

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
    $msg[] = 'This command will remove all data in your configured databases and initialize the sympal database.';
    $msg[] = 'Are you sure you want to proceed? (y/N)';
    $type = 'ERROR_LARGE';

    if (!$options['no-confirmation'] && !$this->askConfirmation($msg, $type, false))
    {
      $this->logSection('sympal', 'Install task aborted');

      return 1;
    }

    // setup some arguments
    $install->setParam('email_address', $options['email']);
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

    // Set the install to not excess log if trace is off
    if (!$this->commandApplication->withTrace())
    {
      $install->setTrace(false);
    }
    
    // run the install
    $this->logSection('sympal', 'Installation starting. run "sympal:install -t" to see debug output');
    $this->log(null);
    $install->install();

    // display a nice message
    $this->log(null);
    $this->logSection('sympal', sprintf('sympal was installed successfully...', $arguments['application']), null, 'COMMENT');

    $url = 'http://localhost/'.$arguments['application'].'_dev.php/security/signin';
    $this->logSection('sympal', sprintf('Open your browser to "%s"', $url), null, 'COMMENT');
    $this->logSection('sympal', sprintf('You can signin with the username "%s" and password "%s"', $install->getParam('username'), $install->getParam('password')), null, 'COMMENT');
  }
}