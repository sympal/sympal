<?php

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

$classes = array(
  'sfSympalServerCheck',
  'sfSympalServerCheckRenderer',
  'sfSympalServerCheckCliRenderer',
  'sfSympalServerCheckUnit'
);
foreach ($classes as $file)
{
  $code = fileGetContents('http://svn.symfony-project.org/plugins/sfSympalPlugin/trunk/lib/check/'.$file.'.class.php');
  file_put_contents(sys_get_temp_dir().'/'.$file.'.class.php', $code);
  require sys_get_temp_dir().'/'.$file.'.class.php';
}

class sfSympalServerCheckInstallRenderer extends sfSympalServerCheckCliRenderer
{
  public function hasErrors()
  {
    return !empty($this->_errors) ? true : false;
  }

  public function hasWarnings()
  {
    return !empty($this->_warnings) ? true : false;
  }
}

$error = false;
try {
  $check = new sfSympalServerCheck();
  $renderer = new sfSympalServerCheckInstallRenderer($check);
  $renderer->setTask($this);
  $renderer->render();
} catch (Exception $e) {
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

$this->logSection('sympal', '...setup database');

$db = setupDatabase($this);

$this->runTask('configure:database', array(
  'dsn' => $db['dsn'],
  'username' => $db['username'],
  'password' => $db['password']
));

$this->logSection('install', 'create an application');
$this->runTask('generate:app', $application);

// i18n
class sfValidatorSympalCultures extends sfValidatorString
{
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);

    $this->setOption('empty_value', array());
    $this->setOption('required', false);
    $this->setOption('trim', true);

    $this->addMessage('invalid', 'Please enter a comma separated list of cultures.');
  }

  public function doClean($value)
  {
    $parts = explode(',', $value);

    $cultures = array();

    foreach ($parts as $culture)
    {
      $culture = trim($culture);

      if (!empty($culture))
      {
        $cultures[] = $culture;
      }  
    }

    if (empty($cultures))
    {
      throw new sfValidatorError($this, 'invalid');
    }

    return $cultures;
  }
}

$this->logBlock("
The next input allows you to turn on internationalisation for your Sympal project.
If you don't need internationalisation simply leave it blank.
But if you want internationalisation enter a comma separated list of cultures,
example: en,de,fr
The first one will be configured as default culture.
", 'COMMENT');

$cultures = $this->askAndValidate("Enter a comma separated list of cultures or leave blank if you don't need i18n:", new sfValidatorSympalCultures(), array('style' => 'QUESTION_LARGE'));

if (count($cultures) > 0)
{
  $this->logSection('i18n', 'enabling i18n in Sympal with cultures: '.implode(', ', $cultures));

  $out = $err = null;
  $command = sprintf(
    '%s "%s" %s',
    sfToolkit::getPhpCli(),
    sfConfig::get('sf_root_dir').'/symfony',
    'sympal:configure '.sprintf('i18n=true language_codes="[%s]"', implode(',', $cultures))
  );
  $this->logBlock($command, 'INFO');
  $this->getFilesystem()->execute($command, $out, $err);

  $this->logSection('i18n', sprintf('enabling i18n in application "%s"', $application));

  $settingsFilename = sfConfig::get('sf_apps_dir').'/'.$application.'/config/settings.yml';
  $settings = file_get_contents($settingsFilename);
  $settings .= <<<EOF

    i18n: true
    default_culture: {$cultures[0]}
EOF;

  file_put_contents($settingsFilename, $settings);
}

// execute sympal installation
$out = $err = null;
$command = sprintf(
  '%s "%s" %s',
  sfToolkit::getPhpCli(),
  sfConfig::get('sf_root_dir').'/symfony',
  'sympal:install '.$application.' --force-reinstall --email-address="'.$emailAddress.'" --username="'.$username.'" --password="'.$password.'" --no-confirmation --db-dsn="'.$db['dsn'].'" --db-username="'.$db['username'].'" --db-password="'.$db['password'].'" --first-name="'.$firstName.'" --last-name="'.$lastName.'"'
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