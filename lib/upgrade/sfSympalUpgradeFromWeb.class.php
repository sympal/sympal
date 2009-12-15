<?php

class sfSympalUpgradeFromWeb extends sfSympalProjectUpgrade
{
  private
    $_currentVersion,
    $_latestVersion,
    $_filesystem;

  public function __construct(ProjectConfiguration $configuration, sfEventDispatcher $dispatcher, sfFormatter $formatter)
  {
    parent::__construct($configuration, $dispatcher, $formatter);

    $this->_filesystem = new sfFilesystem($dispatcher, $formatter);
    $this->_currentVersion = sfSympalConfig::get('current_version', null, sfSympal::VERSION);
  }

  public function hasNewVersion()
  {
    return $this->getLatestVersion() === $this->_currentVersion ? false : true;
  }

  public function getCurrentVersion()
  {
    return sfSympalConfig::get('current_version', null, sfSympal::VERSION);
  }

  public function getUpgradeCommands()
  {
    $rootDir = $this->_configuration->getPluginConfiguration('sfSympalPlugin')->getRootDir();

    $commands = array();
    $commands[] = sprintf('cd %s', dirname($rootDir));
    $commands[] = sprintf('mv sfSympalPlugin sfSympalPlugin_%s', $this->_currentVersion);
    $commands[] = sprintf('svn co http://svn.symfony-project.org/plugins/sfSympalPlugin/tags/%s %s', $this->getLatestVersion(), $rootDir);

    return $commands;
  }

  public function download()
  {
    $this->logSection('sympal', 'Updating Sympal code...');

    $commands = $this->getUpgradeCommands();
    $this->_filesystem->execute(implode('; ', $commands));

    $this->logSection('sympal', 'Sympal code updated successfully...');
  }

  public function getLatestVersion()
  {
    if (!$this->_latestVersion)
    {
      $this->logSection('sympal', 'Checking for new version of Sympal!');

      $code = file_get_contents('http://svn.symfony-project.org/plugins/sfSympalPlugin/trunk/lib/sfSympal.class.php');
      preg_match_all("/const VERSION = '(.*)';/", $code, $matches);
      $this->_latestVersion = $matches[1][0];
    }

    return $this->_latestVersion;
  }
}