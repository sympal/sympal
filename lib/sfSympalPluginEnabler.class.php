<?php

class sfSympalPluginEnabler
{
  private
    $_configuration,
    $_isSympalEnabled = null,
    $_sympalPluginPath = null;

  public function __construct(ProjectConfiguration $configuration)
  {
    $this->_configuration = $configuration;
    $this->_sympalPluginPath = realpath(dirname(__FILE__).'/..');
  }

  public function isSympalEnabled()
  {
    if ($this->_isSympalEnabled === null)
    {
      $this->_isSympalEnabled = true;
      if ($application = sfConfig::get('sf_app'))
      {
        $reflection = new ReflectionClass($application.'Configuration');
        if ($reflection->getConstant('disableSympal'))
        {
          $this->_isSympalEnabled = false;
        }
      }
    }
    return $this->_isSympalEnabled;
  }

  public function enableSympalPlugins()
  {
    if (!$this->isSympalEnabled())
    {
      return false;
    }

    $this->_configuration->enablePlugins('sfDoctrinePlugin');
    $this->_configuration->enablePlugins('sfSympalPlugin');
    $this->_configuration->setPluginPath('sfSympalPlugin', $this->_sympalPluginPath);

    $this->enableSympalCorePlugins(sfSympalPluginConfiguration::$dependencies);
  }

  public function enableFrontendPlugins()
  {
    if (!$this->isSympalEnabled())
    {
      return false;
    }

    $this->_configuration->enablePlugins('sfDoctrinePlugin');
    $this->_configuration->enablePlugins('sfSympalPlugin');
    $this->_configuration->setPluginPath('sfSympalPlugin', $this->_sympalPluginPath);

    $this->enableSympalCorePlugins(array(
      'sfDoctrineGuardPlugin',
      'sfFeed2Plugin',
      'sfWebBrowserPlugin',
      'sfSympalMenuPlugin',
      'sfSympalPagesPlugin',
      'sfSympalContentListPlugin',
      'sfSympalDataGridPlugin',
      'sfSympalUserPlugin',
      'sfSympalRenderingPlugin',
    ));
  }

  public function enableSympalCorePlugins($plugins)
  {
    foreach ((array) $plugins as $plugin)
    {
      $this->_configuration->enablePlugins($plugin);
      $this->_configuration->setPluginPath($plugin, $this->_sympalPluginPath.'/lib/plugins/'.$plugin);
    }
  }

  public function disableSympalCorePlugins($plugins)
  {
    foreach ((array) $plugins as $plugin)
    {
      $this->_configuration->disablePlugins($plugin);
      $this->_configuration->setPluginPath($plugin, false);
    }
  }

  public function overrideSympalPlugin($plugin, $newPlugin, $newPluginPath = null)
  {
    $this->_configuration->disablePlugins($plugin);
    $this->_configuration->enablePlugins($newPlugin);
    if ($newPluginPath)
    {
      $this->_configuration->setPluginPath($newPlugin, $newPluginPath);
    }
  }
}