<?php

class sfSympalReportStatisticsTask extends sfTaskExtraBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', sfSympalToolkit::getFirstApplication()),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'report-statistics';
    $this->briefDescription = 'Report statistics back to Symfony';

    $this->detailedDescription = <<<EOF
The [sympal:report-statistics|INFO] task reports some statistics back to Symfony.
Like what plugins you are using, versions, etc.

  [./sympal:report-statistics|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $plugins = sfSympalPluginToolkit::getAvailablePlugins();

    $api = new sfSympalPluginApi();
    if ($api->getUsername() && $api->getPassword())
    {
      foreach ($plugins as $plugin)
      {
        $result = $api->put('plugins/'.$plugin.'/users.xml');

        if ($result['status'] == 1)
        {
          $this->logSection('sympal', 'Reported use of "'.$plugin.'"...');
        }
      }
    } else {
      throw new sfException('You must specify a username and password for the Symfony plugins api in your Sympal configuration.');
    }
  }
}