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
}