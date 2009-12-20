<?php

class sfSympalDoctrineTable extends Doctrine_Table
{
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
}