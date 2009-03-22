<?php

class sfSympalPluginManagerDownload extends sfSympalPluginManager
{
  public function download($name)
  {
    $name = sfSympalTools::getShortPluginName($name);
    $pluginName = sfSympalTools::getLongPluginName($name);

    $this->logSection('sympal', 'Download '.$pluginName);

    $success = true;
    try {
      $this->logSection('sympal', 'Attempting to download via pear');

      $pluginInstall = new sfPluginInstallTask($this->dispatcher, $this->formatter);
      $ret = $pluginInstall->run(array($pluginName), array());
      if (!sfSympalTools::isPluginInstalled($pluginName))
      {
        $success = false;
      }
    } catch (Exception $e) {
      $success = false;
    }
    if (!$success)
    {
      $path = sfSympalTools::getPluginDownloadPath($pluginName);
      if (is_dir($path))
      {
        $this->filesystem->mirror($path, sfConfig::get('sf_plugins_dir').'/'.$pluginName, sfFinder::type('dir'));
        $this->filesystem->mirror($path, sfConfig::get('sf_plugins_dir').'/'.$pluginName, sfFinder::type('files'));
      } else {
        $svn = exec('which svn');
        $this->filesystem->sh($svn.' co '.$path.' '.sfConfig::get('sf_plugins_dir').'/'.$pluginName);
      }
    }
  }
}