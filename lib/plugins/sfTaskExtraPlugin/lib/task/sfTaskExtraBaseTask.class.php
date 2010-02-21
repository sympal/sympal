<?php

/**
 * Plugin base task.
 * 
 * @package     sfTaskExtraPlugin
 * @subpackage  task
 * @author      Kris Wallsmith <kris.wallsmith@symfony-project.com>
 * @version     SVN: $Id: sfTaskExtraBaseTask.class.php 25037 2009-12-07 19:45:39Z Kris.Wallsmith $
 */
abstract class sfTaskExtraBaseTask extends sfBaseTask
{
  /**
   * @see doCheckPluginExists()
   */
  public function checkPluginExists($plugin, $boolean = true)
  {
    self::doCheckPluginExists($this, $plugin, $boolean);
  }

  /**
   * Checks if a plugin exists.
   * 
   * The plugin directory must exist and have at least one file or folder
   * inside for that plugin to exist.
   * 
   * @param   string  $plugin
   * @param   boolean $boolean Whether to throw exception if plugin exists (false) or doesn't (true)
   * 
   * @throws  sfException If the plugin does not exist
   */
  static public function doCheckPluginExists($task, $plugin, $boolean = true)
  {
    if (in_array($plugin, $task->configuration->getPlugins()))
    {
      // plugin exists if a plugin configuration exists
      $exists = true;
    }
    else
    {
      // otherwise check the plugins directory
      $root = sfConfig::get('sf_plugins_dir').'/'.$plugin;
      $exists = is_dir($root) && count(sfFinder::type('any')->in($root)) > 0;
    }

    if ($boolean != $exists)
    {
      throw new sfException(sprintf($boolean ? 'Plugin "%s" does not exist' : 'Plugin "%s" exists', $plugin));
    }
  }
}
