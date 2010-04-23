<?php

/**
 * This class should be used for all generated modules using the sympal_admin theme
 * 
 * This adds nested set functionality, for example
 * 
 * @package     sfSympalAdminPlugin
 * @subpackage  generator
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */
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