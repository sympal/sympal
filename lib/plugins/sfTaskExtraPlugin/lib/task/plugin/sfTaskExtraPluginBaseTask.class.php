<?php

/**
 * Plugin plugin base task.
 * 
 * @package     sfTaskExtraPlugin
 * @subpackage  task
 * @author      Kris Wallsmith <kris.wallsmith@symfony-project.com>
 * @version     SVN: $Id: sfTaskExtraPluginBaseTask.class.php 25037 2009-12-07 19:45:39Z Kris.Wallsmith $
 */
abstract class sfTaskExtraPluginBaseTask extends sfPluginBaseTask
{
  /**
   * @see sfTaskExtraBaseTask
   */
  public function checkPluginExists($plugin, $boolean = true)
  {
    return sfTaskExtraBaseTask::doCheckPluginExists($this, $plugin, $boolean);
  }
}
