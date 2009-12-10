<?php

if (!$this->askConfirmation('Welcome to the Sympal installer! Do you wish to continue on with the installation? (y/n)', 'QUESTION_LARGE'))
{
  return;
}

$this->logSection('sympal', '...adding Sympal code to ProjectConfiguration');

$manipulator = sfClassManipulator::fromFile(sfConfig::get('sf_config_dir').'/ProjectConfiguration.class.php');
$manipulator->wrapMethod('setup', '', 'require_once(dirname(__FILE__).\'/../plugins/sfSympalPlugin/config/sfSympalPluginConfiguration.class.php\');');
$manipulator->wrapMethod('setup', '', 'sfSympalPluginConfiguration::enableSympalPlugins($this);');
$manipulator->wrapMethod('setup', '', '$this->enableAllPluginsExcept(\'sfPropelPlugin\');');
$manipulator->save();

$this->logSection('sympal', '...downloading sfSympalPlugin');

$this->runTask('plugin:install', 'sfSympalPlugin --stability=alpha');
$this->reloadTasks();

$this->logSection('sympal', '...setup initial data');

$application = $this->askAndValidate('What would you like your first application to be called?', new sfValidatorString());
$firstName   = $this->askAndValidate('What is your first name?', new sfValidatorString());
$lastName    = $this->askAndValidate('What is your last name?', new sfValidatorString());

$validator = new sfValidatorEmail(array(), array('invalid' => 'Invalid e-mail address!'));
$emailAddress = $this->askAndValidate('What is your e-mail address?', $validator);

$this->runTask('configure:author', sprintf("'%s'", $emailAddress));

$username    = $this->askAndValidate('Enter the username for the first user to create:', new sfValidatorString());
$password    = $this->askAndValidate('Enter the password for the first user to create:', new sfValidatorString());

function setupDatabase($task)
{
  $db = array();
  $db['driver']    = $task->askAndValidate('What type of database will you be using? (mysql, pgsql, sqlite)', new sfValidatorString());
  $db['host']      = $task->askAndValidate('Enter the host of your database:', new sfValidatorString());
  $db['name']      = $task->askAndValidate('Enter the name of your database:', new sfValidatorString());
  $db['username']  = $task->askAndValidate('Enter your database username:', new sfValidatorString());
  $db['password']  = $task->askAndValidate('Enter your database password:', new sfValidatorString(array('required' => false)));
  $db['dsn']       = $db['driver'].'://'.$db['username'].':'.$db['password'].'@'.$db['host'].'/'.$db['name'];

  $task->logSection('sympal', sprintf('...testing connection dsn "%s"', $db['dsn']));

  try {
    $conn = Doctrine_Manager::getInstance()->openConnection($db['dsn'], 'test', false);
    $conn->setOption('username', $db['username']);
    $conn->setOption('password', $db['password']);
    $conn->connect();
    $task->logSection('sympal', '...connection credentials valid');
  } catch (Exception $e) {
    $task->logBlock('Connection credentials invalid! Try again!', 'ERROR_LARGE');

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