<?php

/**
 * Sympal symfony installer script.
 *
 * Run the following command to generate a new project with the sympal installer.
 *
 *     $ php /path/to/symfony generate:project sympal --installer=/path/to/installer.php
 *
 * It will setup a base sympal project for you to start working with.
 *
 */

if (!$this instanceof sfGenerateProjectTask)
{
  echo "This script cannot be run outside of generating a new project. See http://www.sympalphp.org \n";
  die;
}

/*
 * ****** Step1: Server checks ***************
 */

$classes = array(
  'sfSympalServerCheck',
  'sfSympalServerCheckRenderer',
  'sfSympalServerCheckCliRenderer',
  'sfSympalServerCheckUnit'
);
foreach ($classes as $file)
{
  $code = fileGetContents('http://github.com/sympal/sympal/raw/master/lib/server_check/'.$file.'.class.php');
  file_put_contents(sys_get_temp_dir().'/'.$file.'.class.php', $code);
  require sys_get_temp_dir().'/'.$file.'.class.php';
}

$error = false;
try {
  $check = new sfSympalServerCheck();
  $renderer = new sfSympalServerCheckCliRenderer($check);
  $renderer->setTask($this);
  $renderer->render();
}
catch (Exception $e)
{
  $this->logBlock($e->getMessage(), 'ERROR_LARGE');
  $error = true;
}

if ($renderer->hasErrors() || $renderer->hasWarnings())
{
  $error = true;
}

if ($error)
{
  $this->logBlock('SYMPAL SERVER CHECK RETURNED ERRORS', 'ERROR_LARGE');

  if (!$this->askConfirmation('The server check returned some errors and/or warnings. Do you wish to continue with the installation? (y/n)', 'QUESTION_LARGE'))
  {
    $this->logBlock('Sympal installation was cancelled!', 'ERROR_LARGE');
    return;
  }
} else {
  if (!$this->askConfirmation('The server check was a success! You should have no problem running Sympal. Do you wish to continue on with the installation? (y/n)', 'QUESTION_LARGE'))
  {
    $this->logBlock('Sympal installation was cancelled!', 'ERROR_LARGE');
    return;
  }
}

/*
 * ****** Step2: Preparing the project ***************
 */

$this->logSection('sympal', '...adding Sympal code to ProjectConfiguration');

$manipulator = sfClassManipulator::fromFile(sfConfig::get('sf_config_dir').'/ProjectConfiguration.class.php');
$manipulator->wrapMethod('setup', '', 'require_once(dirname(__FILE__).\'/../plugins/sfSympalPlugin/config/sfSympalPluginConfiguration.class.php\');');
$manipulator->wrapMethod('setup', '', 'sfSympalPluginConfiguration::enableSympalPlugins($this);');
$manipulator->save();

/*
 * ****** Step3: Downloading sympal ***************
 */

$this->logSection('sympal', '...downloading sfSympalPlugin');

// Using git for now because PEAR ALWAYS FAILS FOR SOME PEOPLE
exec('git clone git://github.com/sympal/sympal.git plugins/sfSympalPlugin');

$rootdir = getcwd();
$this->logSection('sympal', 'Updating sympal submodules');
chdir($rootdir.'/plugins/sfSympalPlugin');
exec('git submodule init');
exec('git submodule update');

$this->logSection('sympal', 'Updating sfInlineObjectPlugin submodules');
chdir($rootdir.'/plugins/sfSympalPlugin/lib/plugins/sfInlineObjectPlugin');
exec('git submodule init');
exec('git submodule update');
chdir($rootdir);

// reload the tasks so we have the symfony tasks
$this->reloadTasks();

/*
 * ****** Step4: Collecting Information & Configuring sympal ***************
 */

$application = $this->askAndValidate(
  'What would you like your first application to be called? An application with this name will be generated for you.',
  new sfValidatorString(),
  array('style' => 'QUESTION_LARGE')
);

$validator = new sfValidatorEmail();
$emailAddress = $this->askAndValidate('What is your e-mail address? (will used as author in config/properties.ini)', $validator, array('style' => 'QUESTION_LARGE'));

$this->logSection('sympal', '...setup database');
$db = setupDatabase($this);
 
$this->runTask('configure:database', array(
  'dsn' => $db['dsn'],
  'username' => $db['username'],
  'password' => $db['password']
));

$this->logSection('sympal', 'Configuring author');
$this->runTask('configure:author', sprintf("'%s'", $emailAddress));

$this->logSection('sympal', sprintf('Generating app %s', $application));
$this->runTask('generate:app', $application);

// install sympal
$this->logSection('sympal',
  'Sympal is now installing itself into the symfony application.
  This will take a while, please be patient...'
);

$command = sprintf(
  '%s "%s" %s',
  sfToolkit::getPhpCli(),
  sfConfig::get('sf_root_dir').'/symfony',
  'sympal:install '.$application .' --no-confirmation'
);
passthru($command);


/*
 * Fix permission for common directories
 * 
 * Must be done because we exist from the project generate task prematurely
 * so that we can show the pretty message about how to get to your site
 */
$fixPerms = new sfProjectPermissionsTask($this->dispatcher, $this->formatter);
$fixPerms->setCommandApplication($this->commandApplication);
$fixPerms->setConfiguration($this->configuration);
$fixPerms->run();

// replace tokens, do this since we exit the project install below
$this->replaceTokens();

// symlink the sf directory
$rootdir = getcwd();
$this->getFilesystem()->relativeSymlink(sfConfig::get('sf_symfony_lib_dir').'/../data/web/sf', sfConfig::get('sf_web_dir').'/sf', true);

/*
 * ****** Step5: Give a friendly message ***************
 */
$this->log(null);
$this->logSection('sympal', sprintf('Sympal was installed successfully...', $application));

$url = 'http://localhost/'.$application.'_dev.php/security/signin';
$this->logSection('sympal', sprintf('Open your browser to "%s"', $url));
$this->logSection('sympal', sprintf('You can signin with the username "admin" and password "admin"'));


exit;



/*
 * ********** Functions used by this process ***************
 */


/*
 * Recursively asks for db information and tests the db connection until
 * something works
 */
function setupDatabase($task)
{
  $db = array();
  $db['driver']    = $task->askAndValidate('What type of database will you be using? (mysql, pgsql, sqlite)', new sfValidatorString(), array('style' => 'QUESTION_LARGE'));
  $db['username'] = null;
  $db['password'] = null;

  if ($db['driver'] == 'sqlite')
  {
    $db['path'] = $task->askAndValidate('Enter the filename of your sqlite database:', new sfValidatorString(), array('style' => 'QUESTION_LARGE'));
    if (!strpos($db['path'], '.')) {
      $db['path'] .= '.sqlite';
    }
    $db['dsn']  = 'sqlite:'.sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.$db['path'];
  }
  else
  {
    $db['host']      = $task->askAndValidate('Enter the host of your database (e.g. localhost):', new sfValidatorString(), array('style' => 'QUESTION_LARGE'));
    $db['name']      = $task->askAndValidate('Enter the name of your database:', new sfValidatorString(), array('style' => 'QUESTION_LARGE'));
    $db['username']  = $task->askAndValidate('Enter your database username:', new sfValidatorString(), array('style' => 'QUESTION_LARGE'));
    $db['password']  = $task->askAndValidate('Enter your database password:', new sfValidatorString(array('required' => false)), array('style' => 'QUESTION_LARGE'));
    $db['dsn'] = $db['driver'].'://'.$db['username'].($db['password'] ? ':'.$db['password'] : '' ).'@'.$db['host'].'/'.$db['name'];
  }

  $task->logSection('sympal', sprintf('...testing connection dsn "%s"', $db['dsn']));

  try {
    $conn = Doctrine_Manager::getInstance()->openConnection($db['dsn'], 'test', false);
    $conn->setOption('username', $db['username']);
    $conn->setOption('password', $db['password']);

    try {
      $conn->createDatabase();
    } catch (Exception $e) {}

    $conn->connect();

    $task->logSection('sympal', '...connection credentials valid');
  } catch (Exception $e) {
    $task->logBlock('Connection credentials invalid! Try again! '.$e->getMessage(), 'ERROR_LARGE');

    return setupDatabase($task);
  }
  return $db;
}

// curls out to retrieve the contents of a file
function fileGetContents($url)
{
  $ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}