<?php

class sfSympalPluginConfiguration extends sfPluginConfiguration
{
  public static
    $dependencies = array(
      'sfFormExtraPlugin',
      'sfTaskExtraPlugin',
      'sfFeed2Plugin',
      'sfWebBrowserPlugin',
      'sfSympalUserPlugin',
      'sfSympalMenuPlugin',
      'sfSympalPluginManagerPlugin',
      'sfSympalPagesPlugin',
      'sfSympalContentListPlugin'
    );

  public
    $sympalConfiguration;

  public static function enableSympalPlugins(ProjectConfiguration $configuration, $plugins = array())
  {
    if ($application = sfConfig::get('sf_app'))
    {
      $reflection = new ReflectionClass($application.'Configuration');
      if ($reflection->getConstant('disableSympal'))
      {
        return false;
      }
    }

    $sympalPluginPath = dirname(dirname(__FILE__));
    $configuration->setPluginPath('sfSympalPlugin', $sympalPluginPath);
    $dependencies = self::$dependencies;
    $embeddedPluginPath = $sympalPluginPath.'/lib/plugins';
    foreach ($dependencies as $plugin)
    {
      $configuration->setPluginPath($plugin, $embeddedPluginPath.'/'.$plugin);
    }
  }

  public function initialize()
  {
    $this->sympalConfiguration = sfSympalConfiguration::getSympalConfiguration($this->dispatcher, $this->configuration);
  }

  public function getSympalConfiguration()
  {
    return $this->sympalConfiguration;
  }
}