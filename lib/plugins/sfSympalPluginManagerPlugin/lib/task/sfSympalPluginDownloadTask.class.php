<?php

class sfSympalPluginDownloadTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('name', sfCommandArgument::OPTIONAL, 'The name of the functionality. i.e. sfSympal#NAME#Plugin'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'sympal'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('list-available', null, sfCommandOption::PARAMETER_NONE, 'List the available sympal plugins.'),
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
    if (isset($options['list-available']) && $options['list-available'])
    {
      $this->logSection('sympal', 'Check sources for sympal plugins');

      $plugins = sfSympalPluginToolkit::getAvailablePlugins();
      if (!empty($plugins))
      {
        $this->logSection('sympal', 'Found '.count($plugins).' Sympal Plugin(s)');
        $this->logSection('sympal', str_repeat('-', 30));
        foreach ($plugins as $plugin)
        {
          $name = sfSympalPluginToolkit::getShortPluginName($plugin);
          $this->logSection('sympal', $plugin);
          $this->logSection('sympal', "\$ php symfony sympal:plugin-download ".$name." --install");
        }
      } else {
        throw new sfException('No sympal plugins found');
      }
    } else {
      if (!isset($arguments['name']))
      {
        throw new sfException('You must specify the plugin name to download.');
      }

      $databaseManager = new sfDatabaseManager($this->configuration);

      $pluginManager = sfSympalPluginManager::getActionInstance($arguments['name'], 'download', $this->configuration, $this->formatter);
      $pluginManager->download();
    }
  }
}