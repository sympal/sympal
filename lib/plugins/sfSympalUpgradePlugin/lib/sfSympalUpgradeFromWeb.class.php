<?php

/**
 * Upgrade class handling the download
 * 
 * @package     sfSympalUpgradePlugin
 * @subpackage  task
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan.weaver@iostudio.com>
 * @since       2010-03-26
 * @version     svn:$Id$ $Author$
 */
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
    $this->_currentVersion = sfSympalConfig::getCurrentVersion();;
  }

  public function hasNewVersion()
  {
    return version_compare($this->getLatestVersion(), $this->_currentVersion, '>');
  }

  public function getCurrentVersion()
  {
    return sfSympalConfig::getCurrentVersion();
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

  /**
   * Downloads and unpackages the new code
   * 
   * This should be run before upgrade() (else upgrade won't run the new code).
   */
  public function download()
  {
    $this->logSection('sympal', 'Downloading and installing Sympal code...');

    $commands = $this->getUpgradeCommands();
    try
    {
      $result = $this->_filesystem->execute(implode('; ', $commands));
    }
    catch (Exception $e)
    {
      throw new sfException('A problem occurred updating the Sympal code: ' . (string) $e);
    }

    $this->logSection('sympal', 'Sympal code updated successfully...');
  }

  /**
   * Runs the upgrade and then writes the latest version to config
   */
  protected function _doUpgrade()
  {
    parent::_doUpgrade();
    sfSympalConfig::writeSetting('current_version', $this->getLatestVersion());
  }

  public function getLatestVersion()
  {
    if (!$this->_latestVersion)
    {
      $this->logSection('sympal', 'Checking for new version of Sympal!');

      $code = file_get_contents('http://svn.symfony-project.org/plugins/sfSympalPlugin/trunk/config/sfSympalPluginConfiguration.class.php');
      preg_match_all("/const VERSION = '(.*)';/", $code, $matches);
      $this->_latestVersion = $matches[1][0];
    }

    return $this->_latestVersion;
  }
}