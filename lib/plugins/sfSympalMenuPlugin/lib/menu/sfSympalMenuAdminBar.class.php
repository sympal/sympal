<?php
class sfSympalMenuAdminBar extends sfSympalMenuSite
{
  public function render()
  {
    if ($this->checkUserAccess())
    {
      $html = '<ul>';
      foreach ($this->_children as $child)
      {
        $html .= $child->renderChild();
      }
      $html .= '</ul>';
      return $html;
    }
  }

  public function renderChild()
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
      $html .= $this->render();
      $html .= '</div>';
      $html .= '</div>';
    }

    $html .= '</li>';

    return $html;
  }
}