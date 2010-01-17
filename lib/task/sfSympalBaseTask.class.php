<?php

abstract class sfSympalBaseTask extends sfTaskExtraBaseTask
{
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