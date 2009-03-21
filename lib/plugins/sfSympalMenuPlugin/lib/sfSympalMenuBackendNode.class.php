<?php
class sfSympalMenuBackendNode extends sfSympalMenuNode
{
  public function _render($test = null)
  {
    $html  = '<li class="yuimenuitem">';
    if ($this->_route)
    {
      sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
      $options = $this->getOptions();
      $options['class'] = (isset($options['class']) ? $options['class'].' ':null).'yuimenuitemlabel';
      $html .= link_to($this->getLabel(), $this->getRoute(), $options);
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