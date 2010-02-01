<?php

class sfSympalMenuDashboard extends sfSympalMenuAdminMenu
{
  public function render()
  {
    $html = '';
    foreach ($this->_children as $child)
    {
      if ($child->hasChildren())
      {
        $html .= '<div class="sympal_dashboard_box">';
        $html .= '<h2>'.$child->renderLabel().'</h2>';
        $html .= '<ul>';
        $html .= $child->renderChildren();
        $html .= '</ul>';
        $html .= '</div>';
      }
    }
    return $html;
  }
}