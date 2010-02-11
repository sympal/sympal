<?php

/**
 * Custom Sympal Doctrine_Collection class to allow us to add custom Sympal
 * functionality to all Doctrine collections in Sympal.
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalDoctrineCollection extends Doctrine_Collection
{
  /**
   * Get array of values from the collection for the given field
   *
   * @param string $fieldName
   * @return array $values
   */
  public function getAllOfField($fieldName)
  {
    $all = array();
    foreach ($this as $key => $value)
    {
      $all[] = $value->get($field);
    }
    return $all;
  }

  /**
   * Get array of slug values for this collection
   *
   * @return array $slugs
   */
  public function getSlugs()
  {
    return $this->getAllOfField('slug');
  }
}