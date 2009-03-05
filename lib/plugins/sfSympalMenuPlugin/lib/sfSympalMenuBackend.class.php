<?php
class sfSympalMenuBackend extends sfSympalMenu
{
  public function _render()
  {
    $html = '';
    if ($this->hasNodes())
    {
      $html  .= '<ul class="first-of-type">';
      foreach ($this->_nodes as $node)
      {
        $html .= $node;
      }
      $html .= '</ul>';
    }
    return $html;
  }
}