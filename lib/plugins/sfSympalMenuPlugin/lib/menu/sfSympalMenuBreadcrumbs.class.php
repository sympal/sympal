<?php

class sfSympalMenuBreadcrumbs extends sfSympalMenuSite
{
  public static function generate($breadcrumbsArray)
  {
    $breadcrumbs = new self('Breadcrumbs');

    $count = 0;
    $total = count($breadcrumbsArray);
    foreach ($breadcrumbsArray as $name => $route)
    {
      $count++;
      if ($count == $total)
      {
        $breadcrumbs->addChild($name);
      } else {
        $breadcrumbs->addChild($name, $route);
      }
    }

    return $breadcrumbs;
  }

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