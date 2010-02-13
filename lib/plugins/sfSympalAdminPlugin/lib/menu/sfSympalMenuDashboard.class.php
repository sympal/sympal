<?php

class sfSympalMenuDashboard extends sfSympalMenuAdminMenu
{
  protected $_dashboardTopLinks = '';

  public function render()
  {
    $html = '';
    foreach ($this->_children as $child)
    {
      if ($child->hasChildren())
      {
        $html .= '<div class="sympal_dashboard_box">';
        $html .= '<h2>'.$child->renderLink().'</h2>';
        $html .= '<ul>';
        $html .= $child->renderChildren();
        $html .= '</ul>';
        $html .= '</div>';
      } else {
        $this->_dashboardTopLinks .= '<div class="sympal_dashboard_top_link">';
        $this->_dashboardTopLinks .= $child->renderLink();
        $this->_dashboardTopLinks .= '</div>';
      }
    }
    if ($this->_dashboardTopLinks)
    {
      $html = '<div class="sympal_dashboard_top_links">'.$this->_dashboardTopLinks.'</div><div class="sympal_dashboard_boxes">'.$html.'</div>';
    }
    return $html;
  }
}