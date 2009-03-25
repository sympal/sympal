<?php
class sfSympalMenuBreadcrumbs extends sfSympalMenuSite
{
  public function getPathAsString()
  {
    $children = array();
    foreach ($this->_children as $child)
    {
      $children[] = $child->getLabel();
    }

    return implode(' > ', $children);
  }
}