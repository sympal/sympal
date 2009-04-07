<?php
class sfSympalMenuBreadcrumbs extends sfSympalMenuSite
{
  public function getPathAsString()
  {
    $children = array();
    foreach ($this->_children as $child)
    {
      $children[] = $child->renderLabel();
    }

    return implode(' > ', $children);
  }

  public function __toString()
  {
    return '<div id="sympal_breadcrumbs">'.parent::__toString().'</div>';
  }
}