<?php

if (!$this->askConfirmation('Welcome to the Sympal installer! Do you wish to continue on with the installation? (y/n)', 'QUESTION_LARGE'))
{
  $this->logBlock('Sympal installation was cancelled!', 'ERROR_LARGE');
  return;
}

$this->logSection('sympal', '...adding Sympal code to ProjectConfiguration');

$manipulator = sfClassManipulator::fromFile(sfConfig::get('sf_config_dir').'/ProjectConfiguration.class.php');
$manipulator->wrapMethod('setup', '', 'require_once(dirname(__FILE__).\'/../plugins/sfSympalPlugin/config/sfSympalPluginConfiguration.class.php\');');
$manipulator->wrapMethod('setup', '', 'sfSympalPluginConfiguration::enableSympalPlugins($this);');
$manipulator->wrapMethod('setup', '', '$this->enableAllPluginsExcept(\'sfPropelPlugin\');');
$manipulator->save();

$this->logSection('sympal', '...downloading sfSympalPlugin');

// Using SVN for now because PEAR ALWAYS FAILS FOR SOME PEOPLE
$this->getFilesystem()->execute('svn co http://svn.symfony-project.org/plugins/sfSympalPlugin/trunk plugins/sfSympalPlugin');

//@$this->runTask('plugin:install', 'sfSympalPlugin --stability=alpha');
//$this->disablePlugin('sfSympalPlugin'); // We don't want the explicit enabling of this plugin
$this->reloadTasks();

$this->logSection('sympal', '...setup initial data');

$application = $this->askAndValidate('What would you like your first application to be called?', new sfValidatorString(), array('style' => 'QUESTION_LARGE'));
$firstName   = $this->askAndValidate('What is your first name?', new sfValidatorString(), array('style' => 'QUESTION_LARGE'));
$lastName    = $this->askAndValidate('What is your last name?', new sfValidatorString(), array('style' => 'QUESTION_LARGE'));

$validator = new sfValidatorEmail(array(), array('invalid' => 'Invalid e-mail address!'));
$emailAddress = $this->askAndValidate('What is your e-mail address?', $validator, array('style' => 'QUESTION_LARGE'));

$this->runTask('configure:author', sprintf("'%s'", $emailAddress));

$username = $this->askAndValidate('Enter the username for the first user to create:', new sfValidatorString(), array('style' => 'QUESTION_LARGE'));
$password = $this->askAndValidate('Enter the password for the first user to create:', new sfValidatorString(), array('style' => 'QUESTION_LARGE'));

function setupDatabase($task)
{
  $db = array();
  $db['driver']    = $task->askAndValidate('What type of database will you be using? (mysql, pgsql, sqlite)', new sfValidatorString(), array('style' => 'QUESTION_LARGE'));
  $db['username'] = null;
  $db['password'] = null;

  if ($db['driver'] == 'sqlite')
  {
    $db['path'] = $task->askAndValidate('Enter the path to your sqlite database:', new sfValidatorString(), array('style' => 'QUESTION_LARGE'));
    $db['dsn']  = 'sqlite:///'.$db['path'];
  } else {
    $db['host']      = $task->askAndValidate('Enter the host of your database:', new sfValidatorString(), array('style' => 'QUESTION_LARGE'));
    $db['name']      = $task->askAndValidate('Enter the name of your database:', new sfValidatorString(), array('style' => 'QUESTION_LARGE'));
    $db['username']  = $task->askAndValidate('Enter your database username:', new sfValidatorString(), array('style' => 'QUESTION_LARGE'));
    $db['password']  = $task->askAndValidate('Enter your database password:', new sfValidatorString(array('required' => false)), array('style' => 'QUESTION_LARGE'));
    $db['dsn'] = $db['driver'].'://'.$db['username'].':'.$db['password'].'@'.$db['host'].'/'.$db['name'];
  }

  $task->logSection('sympal', sprintf('...testing connection dsn "%s"', $db['dsn']));

  try {
    $conn = Doctrine_Manager::getInstance()->openConnection($db['dsn'], 'test', false);
    $conn->setOption('username', $db['username']);
    $conn->setOption('password', $db['password']);
    $conn->connect();
    $task->logSection('sympal', '...connection credentials valid');
  } catch (Exception $e) {
    $task->logBlock('Connection credentials invalid! Try again! '.$e->getMessage(), 'ERROR_LARGE');

    return setupDatabase($task);
  }
  return $db;
}

$this->logSection('sympal', '...setup database');

$db = setupDatabase($this);

$this->runTask('configure:database', array(
  'dsn' => $db['dsn'],
  'username' => $db['username'],
  'password' => $db['password']
));

$this->logSection('install', 'create an application');
$this->runTask('generate:app', $application);

$out = $err = null;
$command = sprintf(
  '%s "%s" %s',
  sfToolkit::getPhpCli(),
  sfConfig::get('sf_root_dir').'/symfony',
  'sympal:install '.$emailAddress.' '.$username.' '.$password.' --no-confirmation --db-dsn="'.$db['dsn'].'" --db-username="'.$db['username'].'" --db-password="'.$db['password'].'" --first-name="'.$firstName.'" --last-name="'.$lastName.'" --application='.$application
);
$this->logBlock($command, 'INFO');
$this->getFilesystem()->execute($command, $out, $err);

// fix permission for common directories
$fixPerms = new sfProjectPermissionsTask($this->dispatcher, $this->formatter);
$fixPerms->setCommandApplication($this->commandApplication);
$fixPerms->setConfiguration($this->configuration);
$fixPerms->run();

$this->replaceTokens();

$this->log(null);
$this->logSection('sympal', sprintf('Sympal was installed successfully...', $application));

$url = 'http://localhost/'.$application.'_dev.php/security/signin';
$this->logSection('sympal', sprintf('Open your browser to "%s"', $url));
$this->logSection('sympal', sprintf('You can signin with the username "%s" and password "%s"', $username, $password));

exit;