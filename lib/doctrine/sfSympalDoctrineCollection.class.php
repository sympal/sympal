<?php

class sfSympalDoctrineCollection extends Doctrine_Collection
{
  public function getAllOfField($field)
  {
    $all = array();
    foreach ($this as $key => $value)
    {
      $all[] = $value->get($field);
    }
    return $all;
  }

  public function getSlugs()
  {
    return $this->getAllOfField('slug');
  }
}