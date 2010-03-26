<?php

/**
 * Class responsible for enabling, disabling and overriding Sympal plugins
 *
 * @package   sfSympalPlugin
 * @author    Jonathan H. Wage <jonwage@gmail.com>
 * @author    Ryan Weaver <ryan.weaver@iostudio.com>
 */
class sfSympalPluginEnabler
{
  private
    $_configuration,
    $_isSympalEnabled = null,
    $_sympalPluginPath = null;

  /**
   * Class Constructor
   * 
   * @param sfProjectConfiguration $configuration The project configuration onto which to enable the plugin
   */
  public function __construct(sfProjectConfiguration $configuration)
  {
    $this->_configuration = $configuration;
    $this->_sympalPluginPath = realpath(dirname(__FILE__).'/..');
  }

  /**
   * Check whether or not Sympal is enabled for the current application
   * (or "project" if there is no application in this scope)
   *
   * @return boolean
   */
  public function isSympalEnabled()
  {
    if ($this->_isSympalEnabled === null)
    {
      $this->_isSympalEnabled = true;
      if ($this->_configuration instanceof sfApplicationConfiguration)
      {
        $application = $this->_configuration->getApplication();
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
   * This will enable:
   *   1) Any plugin inside the lib/plugins directory of sfSympalPlugin
   *      that is in the sfSympalPluginConfiguration::corePlugins array
   *   2) Any plugin living inside the project's plugins directory
   *
   * @return boolean Returns false if Sympal is not enabled
   */
  public function enableSympalPlugins()
  {
    if (!$this->isSympalEnabled())
    {
      return false;
    }
    
    // enable sfDoctrinePlugin
    $this->_configuration->enablePlugins('sfDoctrinePlugin');

    // enable sfSympalPlugin
    $this->_configuration->enablePlugins('sfSympalPlugin');
    $this->_configuration->setPluginPath('sfSympalPlugin', $this->_sympalPluginPath);

    $this->enableSympalCorePlugins(sfSympalPluginConfiguration::$corePlugins);

    $plugins = $this->_configuration->getPlugins();
    $foundPlugins = array();
    $finder = sfFinder::type('dir')->maxdepth(0)->ignore_version_control(false)->follow_link()->name('*Plugin');
    foreach ($finder->in(sfConfig::get('sf_plugins_dir')) as $path)
    {
      $foundPlugins[] = basename($path);
    }
    $plugins = array_merge($plugins, $foundPlugins);
    $plugins = array_unique($plugins);
    $this->_configuration->setPlugins($plugins);
    
    return true;
  }

  /**
   * Enable an array of Sympal core plugins
   * 
   * A "core" plugin is any plugin that physically lives inside of the
   * lib/plugins directory of sfSympalPlugin
   *
   * @param array $plugins The core plugins to enable
   * @return void
   */
  public function enableSympalCorePlugins($plugins)
  {
    foreach ((array) $plugins as $plugin)
    {
      $path = $this->_sympalPluginPath.'/lib/plugins/'.$plugin;
      if (!file_exists($path))
      {
        throw new sfException(sprintf(
          'Cannot enable core plugin "%s" - it does not exist in %s',
          $plugin,
          dirname($path)
        ));
      }
      $this->_configuration->enablePlugins($plugin);
      $this->_configuration->setPluginPath($plugin, $path);
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
   * @param string $plugin          The plugin to override
   * @param string $newPluginPath   The path of the new plugin (defaults to the plugins directory)
   * @return void
   */
  public function overrideSympalPlugin($plugin, $newPluginPath = null)
  {
    $this->_configuration->disablePlugins($plugin);
    $this->_configuration->enablePlugins($plugin);
    
    if ($newPluginPath === null)
    {
      $newPluginPath = sfConfig::get('sf_plugins_dir').'/'.$plugin;
    }

    $this->_configuration->setPluginPath($plugin, $newPluginPath);
  }
}