<?php

class sfSympalMenuInlineEditBarButtons extends sfSympalMenu
{
  protected
    $_isEditModeButton = false,
    $_inputClass,
    $_isButton = true;

  public function setInputClass($class)
  {
    $this->_inputClass = $class;
  }

  public function isButton($bool = null)
  {
    if ($bool !== null)
    {
      $this->_isButton = $bool;
      return $this;
    }
    return $this->_isButton;
  }

  public function isEditModeButton($bool = null)
  {
    if ($bool !== null)
    {
      $this->_isEditModeButton = $bool;
      return $this;
    }
    return $this->_isEditModeButton;
  }

  public function renderChildBody()
  {
    if ($this->_isButton)
    {
      $class = $this->_isEditModeButton ? $this->_inputClass.' sympal_inline_edit_bar_edit_buttons' : $this->_inputClass;
      if ($this->_route)
      {
        $html = '<input type="button" rel="'.url_for($this->_route).'" value="'.$this->renderLabel().'" class="'.$class.'" />';
      } else {
        $html = '<input type="button" value="'.$this->renderLabel().'" class="'.$class.'" />';
      }
    } else {
      $html = parent::renderChildBody();
    }
    return $html;
  }
}