<?php

/**
 * Custom Sympal Doctrine_Query class to allow us to add custom Sympal
 * functionality to all Doctrine queries in Sympal.
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalDoctrineQuery extends Doctrine_Query
{
  /**
   * Enable Sympal result cache for this query if it is enabled via configuration
   *
   * @param string $key 
   * @return Doctrine_Query $query
   */
  public function enableSympalResultCache($key)
  {
    if ($lifetime = sfSympalConfig::shouldUseResultCache($key))
    {
      $this->useResultCache(true, $lifetime, $key);
    }

    return $this;
  }
}