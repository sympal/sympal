<?php

class sfSympalPluginListTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'sympal'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('search', null, sfCommandOption::PARAMETER_OPTIONAL, 'Search for a sympal plugin.'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'plugin-list';
    $this->briefDescription = 'List/search through all the available sympal plugins.';

    $this->detailedDescription = <<<EOF
The [sympal:plugin-list|INFO] is a task to list all the available sympal plugins.
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $plugins = sfSympalPluginToolkit::getAvailablePlugins();

    if (isset($options['search']) && $options['search'])
    {
      $this->logSection('sympal', 'Searching for "'.$options['search'].'"');

      $this->search = $options['search'];
      $plugins = array_filter($plugins, array($this, '_searchPlugins'));
    }

    if (!empty($plugins))
    {
      $this->logSection('sympal', 'Found '.count($plugins).' Sympal Plugin(s)');
      $this->logSection('sympal', str_repeat('-', 30));
      foreach ($plugins as $plugin)
      {
        $name = sfSympalPluginToolkit::getShortPluginName($plugin);
        $this->logSection('sympal', $plugin);
        $this->logSection('sympal', "\$ php symfony sympal:plugin-download ".$name." --install");
        $this->logSection('sympal', null);
      }
    } else {
      throw new sfException('No sympal plugins found');
    }
  }

  protected function _searchPlugins($value)
  {
    return stristr($value, $this->search);
  }
}