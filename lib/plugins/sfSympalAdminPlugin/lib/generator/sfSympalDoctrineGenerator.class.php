<?php

class sfSympalDoctrineGenerator extends sfDoctrineGenerator
{
  public function isNestedSet()
  {
    return $this->table->hasTemplate('Doctrine_Template_NestedSet');
  }
}