<?php
class sfSympalMenuBreadcrumbs extends sfSympalMenu
{
  public function getPathAsString()
  {
    $nodes = array();
    foreach ($this->_nodes as $node)
    {
      $nodes[] = $node->getLabel();
    }
    return implode(' > ', $nodes);
  }
}