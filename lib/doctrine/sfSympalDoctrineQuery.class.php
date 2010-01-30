<?php

class sfSympalDoctrineQuery extends Doctrine_Query
{
  public function enableSympalResultCache($key)
  {
    if ($lifetime = sfSympalConfig::shouldUseResultCache($key))
    {
      $this->useResultCache(true, $lifetime, $key);
    }

    return $this;
  }
}