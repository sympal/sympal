<?php

/**
 * Abstract base class for all Sympal tasks to extend from for convenience methods
 * and shortcuts.
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
abstract class sfSympalBaseTask extends sfTaskExtraBaseTask
{
  /**
   * Check if we are in unix or not
   *
   * @return boolean
   */
  public function isUnix()
  {
    return DIRECTORY_SEPARATOR == '/';
  }

  /**
   * Check if we are in windows or not
   *
   * @return boolean
   */
  public function isWindows()
  {
    return !$this->isUnix();
  }

  /**
   * Get the current sfSympalContext instance
   *
   * @return sfSympalContext $sympalContext
   */
  public function getSympalContext()
  {
    return sfSympalContext::getInstance();
  }

  /**
   * Use a database in this task
   *
   * @return void
   */
  public function useDatabase()
  {
    return new sfDatabaseManager($this->configuration);
  }

  /**
   * Shortcut to clear the cache in a task
   *
   * @param array $options 
   * @return void
   */
  public function clearCache(array $options = array())
  {
    $task = new sfCacheClearTask($this->dispatcher, $this->formatter);
    $task->run(array(), $options);
  }
}