<?php

class sfSympalPluginDownloadTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'The name of the functionality. i.e. sfSympal#NAME#Plugin'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'sympal'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('list-available', null, sfCommandOption::PARAMETER_NONE, 'List the available sympal plugins.'),
      new sfCommandOption('install', null, sfCommandOption::PARAMETER_NONE, 'Install the plugin after downloading.'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'plugin-download';
    $this->briefDescription = 'Download a sympal plugin to an existing sympal installation';

    $this->detailedDescription = <<<EOF
The [sympal:plugin-download|INFO] is a task to download a plugin to an existing sympal installation.
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $name = $arguments['name'];
    $pluginName = 'sfSympal'.Doctrine_Inflector::classify($name).'Plugin';

    $path = 'http://svn.symfony-project.com/plugins';

    $html = file_get_contents($path);

    if (isset($options['list-available']) && $options['list-available'])
    {
      $this->logSection('sympal', 'Check '.$path.' for sympal plugins');

      preg_match_all("/sfSympal(.*)Plugin/", strip_tags($html), $matches);
      $installedPlugins = $this->configuration->getPlugins();
      $plugins = array_diff($matches[0], $installedPlugins);
      if (!empty($plugins))
      {
        foreach ($plugins as $plugin)
        {
          $this->logSection('sympal', $plugin);
        }
      } else {
        throw new sfException('No sympal plugins found');
      }
    } else {
      if (strstr($html, $pluginName))
      {
        $this->logSection('sympal', 'Download '.$pluginName);

        try {
          $this->logSection('sympal', 'Attempting to download view pear');
  
          $pluginInstall = new sfPluginInstallTask($this->dispatcher, $this->formatter);
          $pluginInstall->setCommandApplication($this->commandApplication);
          $ret = $pluginInstall->run(array($pluginName), array());
        } catch (Exception $e) {
          $this->logSection('sympal', 'Download via PEAR failed. Trying SVN.');
          $svn = exec('which svn');
          $e = explode('.', SYMFONY_VERSION);
          $version = $e[0].'.'.$e[1];
          $branchSvnPath = $path.'/'.$pluginName.'/branches/'.$version;
          $trunkSvnPath = $path.'/'.$pluginName.'/trunk';

          if (@file_get_contents($branchSvnPath) !== false)
          {
            $this->getFilesystem()->sh($svn.' co '.$branchSvnPath.' '.sfConfig::get('sf_plugins_dir').'/'.$pluginName);
          } else if (@file_get_contents($trunkSvnPath) !== false) {
            $this->getFilesystem()->sh($svn.' co '.$trunkSvnPath.' '.sfConfig::get('sf_plugins_dir').'/'.$pluginName);
          } else {
            throw new sfException('Failed to download '.$pluginName.' via SVN');
          }
        }

        if (isset($options['install']) && $options['install'])
        {
          $install = new sfSympalPluginInstallTask($this->dispatcher, $this->formatter);
          $install->setCommandApplication($this->commandApplication);
          $ret = $install->run(array($name), array());
        }
      } else {
        throw new sfException($pluginName.' does not exist.');
      }
    }
  }
}