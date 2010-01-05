<?php

class sfSympalDoctrineGenerator extends sfDoctrineGenerator
{
  public function isNestedSet()
  {
    return $this->table->hasTemplate('Doctrine_Template_NestedSet');
  }

  public function getNestedSetIndention()
  {
    return sprintf("str_repeat(' &nbsp; &nbsp; ', %s)", $this->getColumnGetter('level', true)).
      ".image_tag('/sfSympalPlugin/images/'.(!".$this->getObjectMethod('getNode()->isLeaf', true)." ? 'folder':'page').'.png').' '";
  }

  public function getObjectMethod($method, $developed = false, $prefix = '')
  {
    if ($developed)
    {
      $method = sprintf('$%s%s->%s()', $prefix, $this->getSingularName(), $method);
    }
    return $method;
  }
}