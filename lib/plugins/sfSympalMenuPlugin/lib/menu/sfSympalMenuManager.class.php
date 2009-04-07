<?php

class sfSympalMenuManager extends sfSympalMenuSite
{
  protected static
    $_dragDrops = array();

  public function render()
  {
    $html = '';

    $id = Doctrine_Inflector::urlize($this->getName()).'-menu';
    if ($this->getLevel() == 0)
    {
      $html .= '<ul>';
      $html .= '<li>';
      $html .= $this->renderChildBody();
    }

    if ($this->checkUserAccess() && $this->hasChildren())
    {
      $html .= '<ul id="'.$id.'-children">';
      foreach ($this->_children as $child)
      {
        $html .= $child->renderChild();
      }
      $html .= '</ul>';
    }

    if ($this->getLevel() == 0)
    {
      $html .= '</li>';
      $html .= '</ul>';
    }

    return $html;
  }

  public function renderChild()
  {
    if ($this->checkUserAccess())
    {
      $id = Doctrine_Inflector::urlize($this->getName());
      $html = '<li id="'.$id.'" class="'.($this->isCurrent() ? ' current':null).'">';
      $html .= $this->renderChildBody();
      if ($this->hasChildren() && $this->showChildren())
      {
        $html .= $this->render();
      }
      $html .= '</li>';

      return $html;
    }
  }

  public function renderChildBody()
  {
    $html  = '<a href="#">';
    $html .= '<span class="ygtvlabel" id="node-'.$this->getMenuItem()->getId().'">';
    $html .= $this->renderLabel();
    $html .= '</span>';
    $html .= '</a>';

    if ($this->getLevel() > 0)
    {
      self::$_dragDrops[] = $this->getMenuItem()->getId();
    }

    return $html;
  }

  public static function getDragDrops()
  {
    $dragDrops = array_unique(self::$_dragDrops);
    self::$_dragDrops = array();

    $dd = '';
    foreach ($dragDrops as $id)
    {
      $dd .= "new DDSend(\"node-".$id."\");\n";
    }
    return $dd;
  }
}