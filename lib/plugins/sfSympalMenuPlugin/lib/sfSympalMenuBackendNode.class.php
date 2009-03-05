<?php
class sfSympalMenuBackendNode extends sfSympalMenuNode
{
  public function _render()
  {
    $html  = '<li class="yuimenuitem">';
    if ($this->_route)
    {
      sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
      $html .= link_to($this->getLabel(), $this->getRoute(), $this->getOptions(), 'class=yuimenuitemlabel');
    } else {
      $html .= '<a href="#" class="yuimenuitemlabel">'.$this->getLabel().'</a>';
    }
    if ($this->hasNodes())
    {
      $html .= '<div class="yuimenu">';
      $html .= '<div class="bd">';
      $html .= '<ul class="first-of-type">';
      foreach ($this->_nodes as $node)
      {
        $html .= $node;
      }
      $html .= '</ul>';
      $html .= '</div>';
      $html .= '</div>';
    }
    $html .= '</li>';
    return $html;
  }
}