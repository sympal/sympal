<?php

abstract class sfSympalBaseTask extends sfTaskExtraBaseTask
{
  public function isUnix()
  {
    return DIRECTORY_SEPARATOR == '/';
  }

  public function isWindows()
  {
    return !$this->isUnix();
  }

  public function getSympalContext()
  {
    return sfSympalContext::getInstance();
  }

  public function useDatabase()
  {
    return new sfDatabaseManager($this->configuration);
  }

  public function clearCache(array $options = array())
  {
    $task = new sfCacheClearTask($this->dispatcher, $this->formatter);
    $task->run(array(), $options);
  }
}