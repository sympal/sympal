<?php

/**
 * Tests symfony tasks.
 * 
 * @package     sfTaskExtraPlugin
 * @subpackage  test
 * @author      Kris Wallsmith <kris.wallsmith@symfony-project.com>
 * @version     SVN: $Id: sfTaskExtraLimeTest.class.php 25032 2009-12-07 17:17:38Z Kris.Wallsmith $
 */
class sfTaskExtraLimeTest extends lime_test
{
  public $configuration;

  /**
   * Executes a task and tests its success.
   * 
   * @param   string  $taskClass
   * @param   array   $arguments
   * @param   array   $options
   * @param   boolean $boolean
   * 
   * @return  boolean
   */
  public function task_ok($taskClass, array $arguments = array(), array $options = array(), $boolean = true, $message = null)
  {
    if (null === $message)
    {
      $message = sprintf('"%s" execution %s', $taskClass, $boolean ? 'succeeded' : 'failed');
    }

    chdir(dirname(__FILE__).'/../../test/fixtures/project');

    $task = new $taskClass($this->configuration->getEventDispatcher(), new sfFormatter());
    $task->setConfiguration($this->configuration);
    try
    {
      $ok = $boolean === $task->run($arguments, $options) ? false : true;
    }
    catch (Exception $e)
    {
      $ok = $boolean === false;
    }

    $this->ok($ok, $message);

    if (isset($e) && !$ok)
    {
      $this->diag('    '.$e->getMessage());
    }

    return $ok;
  }
}
