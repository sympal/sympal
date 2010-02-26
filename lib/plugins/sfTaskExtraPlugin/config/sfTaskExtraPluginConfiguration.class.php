<?php

/**
 * Plugin configuration.
 * 
 * @package     sfTaskExtraPlugin
 * @subpackage  config
 * @author      Kris Wallsmith <kris.wallsmith@symfony-project.com>
 * @version     SVN: $Id: sfTaskExtraPluginConfiguration.class.php 26197 2010-01-04 23:26:59Z Kris.Wallsmith $
 */
class sfTaskExtraPluginConfiguration extends sfPluginConfiguration
{
  protected
    $connectedPlugins = array();

  /**
   * @see sfPluginConfiguration
   */
  public function configure()
  {
    $this->dispatcher->connect('configuration.method_not_found', array($this, 'listenForConfigurationMethodNotFound'));

    $this->dispatcher->connect('command.pre_command', array($this, 'listenForPreCommand'));
    $this->dispatcher->connect('command.post_command', array($this, 'listenForPostCommand'));
  }

  /**
   * Connects a plugin to some extra automation.
   * 
   * @param array|string $names
   */
  public function connectPlugins($names)
  {
    if (!is_array($names))
    {
      $names = array($names);
    }

    foreach ($names as $name)
    {
      $this->connectedPlugins[$name] = $this->configuration->getPluginConfiguration($name);
      $this->connectedPlugins[$name]->connectTests();
    }
  }

  /**
   * Returns an array of subpaths for the connected plugins.
   * 
   * @param   string $path
   * 
   * @return  array
   */
  public function getConnectedPluginSubPaths($subpath)
  {
    $paths = array();

    foreach ($this->connectedPlugins as $name => $plugin)
    {
      $path = $plugin->getRootDir().$subpath;

      if (file_exists($path))
      {
        $paths[] = $path;
      }
    }

    return $paths;
  }

  /**
   * Listens for the 'configuration.method_not_found' event.
   * 
   * @param   sfEvent $event
   * 
   * @return  boolean
   */
  public function listenForConfigurationMethodNotFound(sfEvent $event)
  {
    if ('enablePluginDevelopment' == $event['method'])
    {
      call_user_func_array(array($this, 'connectPlugins'), $event['arguments']);
      return true;
    }
  }

  /**
   * Listens for the 'command.pre_command' event.
   * 
   * @param   sfEvent $event
   * 
   * @return  boolean
   */
  public function listenForPreCommand(sfEvent $event)
  {
    $task = $event->getSubject();
    $arguments = $event['arguments'];
    $options = $event['options'];

    // set global symfony path for plugin tests
    if ('test' == $task->getNamespace())
    {
      $_SERVER['SYMFONY'] = sfConfig::get('sf_symfony_lib_dir');
    }

    return false;
  }

  /**
   * Listens for the 'command.post_command' event.
   * 
   * @param   sfEvent $event
   * 
   * @return  boolean
   */
  public function listenForPostCommand(sfEvent $event)
  {
    $task = $event->getSubject();

    if ($task instanceof sfPropelBuildModelTask)
    {
      $addon = new sfTaskExtraBuildModelAddon($this->configuration, new sfAnsiColorFormatter());
      $addon->setWrappedTask($task);
      $addon->executeAddon();

      return true;
    }

    return false;
  }
}
