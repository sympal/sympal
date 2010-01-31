<?php

class sfSympalMenuInlineEditBarButtons extends sfSympalMenu
{
  protected
    $_isEditModeButton = false,
    $_inputClass,
    $_isButton = true,
    $_shortcut;

  public function setShortcut($shortcut)
  {
    $this->_shortcut = $shortcut;
    return $this;
  }

  public function setInputClass($class)
  {
    $this->_inputClass = $class;
    return $this;
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
        $html = '<input title="'.$this->_shortcut.'" type="button" rel="'.url_for($this->_route).'" value="'.$this->renderLabel().'" class="'.$class.'" />';
      } else {
        $html = '<input title="'.$this->_shortcut.'" type="button" value="'.$this->renderLabel().'" class="'.$class.'" />';
      }
      
      if ($this->_shortcut)
      {
        $html .= '<script type="text/javascript">$(function() { shortcut.add("'.$this->_shortcut.'", function() { $(\'.'.$this->_inputClass.'\').click(); }); });</script>';
      }
    } else {
      $html = parent::renderChildBody();
    }
    return $html;
  }
}