<?php

class sfSympalPluginManagerDownload extends sfSympalPluginManager
{
  public function download()
  {
    $this->_disableProdApplication();

    $this->logSection('sympal', 'Download '.$this->_pluginName);

    $success = true;
    try {
      $this->logSection('sympal', 'Attempting to download via pear');

      $pluginInstall = new sfPluginInstallTask($this->_dispatcher, $this->_formatter);
      $ret = @$pluginInstall->run(array($this->_pluginName), array());
      if (!sfSympalTools::isPluginInstalled($this->_pluginName))
      {
        $success = false;
      }
    } catch (Exception $e) {
      $success = false;
    }
    if (!$success)
    {
      $path = sfSympalTools::getPluginDownloadPath($this->_pluginName);
      if (is_dir($path))
      {
        $this->filesystem->mirror($path, sfConfig::get('sf_plugins_dir').'/'.$this->_pluginName, sfFinder::type('dir'));
        $this->filesystem->mirror($path, sfConfig::get('sf_plugins_dir').'/'.$this->_pluginName, sfFinder::type('files'));
      } else {
        $svn = exec('which svn');
        $this->filesystem->sh($svn.' co '.$path.' '.sfConfig::get('sf_plugins_dir').'/'.$this->_pluginName);
      }
    }

    $this->_enableProdApplication();
  }
}