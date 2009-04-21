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

    return implode(sfSympalConfig::get('breadcrumbs_separator', null, ' / '), $children);
  }

  public function __toString()
  {
    return '<div id="sympal_breadcrumbs">'.parent::__toString().'</div>';
  }
}