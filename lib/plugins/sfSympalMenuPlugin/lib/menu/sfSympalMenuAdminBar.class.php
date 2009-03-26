<?php
class sfSympalMenuAdminBar extends sfSympalMenuSite
{
  protected function _renderChildren()
  {
    $html = '';
    if ($this->hasChildren())
    {
      $html  .= '<ul class="first-of-type">';
      foreach ($this->_children as $child)
      {
        $html .= $child->_render();
      }
      $html .= '</ul>';
    }

    return $html;
  }

  protected function _render()
  {
    $html  = '<li class="'.Doctrine_Inflector::urlize($this->getName()).' yuimenuitem">';

    if ($this->_route)
    {
      sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
      $options = $this->getOptions();
      $options['class'] = (isset($options['class']) ? $options['class'].' ':null).'yuimenuitemlabesl';
      $html .= link_to($this->getLabel(), $this->getRoute(), $options);
    } else {
      $html .= '<a href="#" class="yuimenuitemlabel">'.$this->getLabel().'</a>';
    }

    if ($this->hasChildren())
    {
      $html .= '<div class="yuimenu">';
      $html .= '<div class="bd">';
      $html .= $this->_renderChildren();
      $html .= '</div>';
      $html .= '</div>';
    }

    $html .= '</li>';

    return $html;
  }
}