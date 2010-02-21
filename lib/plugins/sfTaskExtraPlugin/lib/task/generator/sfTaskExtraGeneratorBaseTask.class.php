<?php

/**
 * Plugin generator base task.
 * 
 * @package     sfTaskExtraPlugin
 * @subpackage  task
 * @author      Kris Wallsmith <kris.wallsmith@symfony-project.com>
 * @version     SVN: $Id: sfTaskExtraGeneratorBaseTask.class.php 25037 2009-12-07 19:45:39Z Kris.Wallsmith $
 */
abstract class sfTaskExtraGeneratorBaseTask extends sfGeneratorBaseTask
{
  /**
   * @see sfTaskExtraBaseTask
   */
  public function checkPluginExists($plugin, $boolean = true)
  {
    sfTaskExtraBaseTask::doCheckPluginExists($this, $plugin, $boolean);
  }
}
