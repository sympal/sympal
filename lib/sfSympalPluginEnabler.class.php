<?php

/**
 * Class responsible for enabling, disabling and overriding Sympal plugins
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
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

  /**
   * Check whether or not Sympal is enabled for the current application
   *
   * @return boolean
   */
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

  /**
   * Enable all Sympal plugins
   *
   * @return boolean Returns false if Sympal is not enabled
   */
  public function enableSympalPlugins()
  {
    $this->_configuration->enablePlugins('sfDoctrinePlugin');

    if (!$this->isSympalEnabled())
    {
      return false;
    }

    $this->_configuration->enablePlugins('sfSympalPlugin');
    $this->_configuration->setPluginPath('sfSympalPlugin', $this->_sympalPluginPath);

    $this->enableSympalCorePlugins(sfSympalPluginConfiguration::$dependencies);

    $plugins = $this->_configuration->getPlugins();
    $finder = sfFinder::type('dir')->maxdepth(0)->ignore_version_control(false)->follow_link()->name('*Plugin');
    foreach ($finder->in(sfConfig::get('sf_plugins_dir')) as $path)
    {
      $plugins[] = basename($path);
    }
    sort($plugins);
    $this->_configuration->setPlugins($plugins);
  }

  /**
   * Enable an array of Sympal core plugins
   *
   * @param array $plugins
   * @return void
   */
  public function enableSympalCorePlugins($plugins)
  {
    foreach ((array) $plugins as $plugin)
    {
      $this->_configuration->enablePlugins($plugin);
      $this->_configuration->setPluginPath($plugin, $this->_sympalPluginPath.'/lib/plugins/'.$plugin);
    }
  }

  /**
   * Disable an array of Sympal core plugins
   *
   * @param array $plugins
   * @return void
   */
  public function disableSympalCorePlugins($plugins)
  {
    foreach ((array) $plugins as $plugin)
    {
      $this->_configuration->disablePlugins($plugin);
      $this->_configuration->setPluginPath($plugin, false);
    }
  }

  /**
   * Override a Sympal plugin with a new plugin
   *
   * @param string $plugin 
   * @param string $newPlugin 
   * @param string $newPluginPath 
   * @return void
   */
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