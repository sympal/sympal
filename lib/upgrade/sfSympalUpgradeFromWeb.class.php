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

  protected function _urlExists($url)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    return ($info['http_code'] == 404 ? false : true);
  }

  public function getUpgradeCommands()
  {
    $pluginDir = $this->_configuration->getPluginConfiguration('sfSympalPlugin')->getRootDir();
    $backupDir = dirname($pluginDir);

    $url = sprintf('http://svn.symfony-project.org/plugins/sfSympalPlugin/tags/%s', $this->getLatestVersion());
    if ($this->_urlExists($url) === false)
    {
      $url = 'http://svn.symfony-project.org/plugins/sfSympalPlugin/trunk';
    }

    $commands = array();
    $commands['cd'] = sprintf('cd %s', dirname($pluginDir));
    $commands['backup'] = sprintf('mv sfSympalPlugin %s/sfSympalPlugin_%s', $backupDir, $this->_currentVersion);
    $commands['download'] = sprintf('svn co %s %s', $url, $pluginDir);

    return $commands;
  }

  public function download()
  {
    $this->logSection('sympal', 'Updating Sympal code...');

    $commands = $this->getUpgradeCommands();
    try {
      $result = $this->_filesystem->execute(implode('; ', $commands));
    } catch (Exception $e) {
      throw new sfException('A problem occurred updating Sympal code.');
    }

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