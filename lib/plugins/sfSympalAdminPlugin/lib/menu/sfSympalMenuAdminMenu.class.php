<?php

class sfSympalMenuAdminMenu extends sfSympalMenu
{
  public function renderChildBody()
  {
    if ($this->_route)
    {
      $html = $this->renderLink();
    } else {
      $html = '<div class="clickable">'.$this->renderLabel().'</div>';
    }
    return $html;
  }
}