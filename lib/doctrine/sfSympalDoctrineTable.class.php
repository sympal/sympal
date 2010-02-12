<?php

/**
 * Custom Sympal Doctrine_Table class to allow us to add custom Sympal
 * functionality to all Doctrine tables in Sympal.
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalDoctrineTable extends Doctrine_Table
{
  /**
   * Get the id of a record from this tables identity map
   *
   * @param mixed $id
   * @return mixed $record
   */
  public function getFromIdentityMap($id)
  {
    $id = (array) $id;
    $id = implode(' ', $id);
    if (isset($this->_identityMap[$id]))
    {
      return $this->_identityMap[$id];
    }
    return false;
  }

  public function search($query)
  {
    return sfSympalSearch::getInstance()->search($this->getOption('name'), $query);
  }

  public function getSearchQuery($query)
  {
    return sfSympalSearch::getInstance()->getDoctrineSearchQuery($this->getOption('name'), $query);
  }
}