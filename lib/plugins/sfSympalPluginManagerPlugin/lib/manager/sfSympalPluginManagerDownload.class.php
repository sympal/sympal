<?php

class sfSympalPluginManagerDownload extends sfSympalPluginManager
{
  public function download()
  {
    $this->logSection('sympal', sprintf('Downloading Sympal plugin "%s"', $this->_pluginName));

    $success = true;
    try {
      $this->logSection('sympal', '...trying to download plugin via PEAR');

      $pluginInstall = new sfPluginInstallTask($this->_dispatcher, $this->_formatter);
      $ret = @$pluginInstall->run(array($this->_pluginName), array());

      if (!sfSympalPluginToolkit::isPluginInstalled($this->_pluginName))
      {
        $this->logSection('sympal', '...could not download plugin via PEAR', null, 'ERROR');
        $success = false;
      }
    } catch (Exception $e) {
      $success = false;
      $this->logSection('sympal', '...exception thrown while downloading from PEAR: '.$e->getMessage(), null, 'ERROR');
    }

    if (!$success)
    {
      $this->logSection('sympal', 'Could not download plugin via PEAR! Trying alternative sources.');

      $path = sfSympalPluginToolkit::getPluginDownloadPath($this->_pluginName);
      if (is_dir($path))
      {
        $this->logSection('sympal', sprintf('...copying plugin from local directory: "%s"', $path));

        $this->_filesystem->mirror($path, sfConfig::get('sf_plugins_dir').'/'.$this->_pluginName, sfFinder::type('dir'));
        $this->_filesystem->mirror($path, sfConfig::get('sf_plugins_dir').'/'.$this->_pluginName, sfFinder::type('files'));
      } else {
        $this->logSection('sympal', sprintf('...checking out plugin from SVN repository: "%s"', $path));

        $svn = exec('which svn');
        $this->_filesystem->execute($svn.' co '.$path.' '.sfConfig::get('sf_plugins_dir').'/'.$this->_pluginName);
      }
    }
  }
}