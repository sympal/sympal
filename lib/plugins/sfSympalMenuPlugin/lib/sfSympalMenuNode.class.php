<?php
class sfSympalMenuNode extends sfSympalMenu
{
  protected
    $_name,
    $_route,
    $_current,
    $_options = array();

  public function __construct($name = null, $route = null, $options = array())
  {
    $this->_name = $name;
    $this->_route = $route;
    $this->_options = $options;
  }

  public function getLabel()
  {
    return (is_array($this->_options) && isset($this->_options['label'])) ? $this->_options['label']:$this->_name;
  }

  public function setLabel($label)
  {
    $this->_options['label'] = $label;
  }

  public function getRoute()
  {
    return $this->_route;
  }

  public function setRoute($route)
  {
    $this->_route = $route;
  }

  public function getOptions()
  {
    return $this->_options;
  }

  public function setOptions($options)
  {
    $this->_options = $options;
  }

  public function getOption($name, $default = null)
  {
    if (isset($this->_options[$name]))
    {
      return $this->_options[$name];
    }
    return $default;
  }

  public function setOption($name, $value)
  {
    $this->_options[$name] = $value;
  }

  public function isCurrent($bool = null)
  {
    if (!is_null($bool))
    {
      $this->_current = $bool;
    }
    return $this->_current;
  }

  public function _render()
  {
    if ($this->checkUserAccess())
    {
      $html = '<li'.($this->isCurrent() ? ' class="current"':null).'>';
      if ($this->_route)
      {
        $options = $this->getOptions();
        if  ($this->isCurrent())
        {
          $options['id'] = 'current';
        }
        sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
        $html .= link_to($this->getLabel(), $this->getRoute(), $options);
      } else {
        $html .= $this->getLabel();
      }
      if ($this->hasNodes())
      {
        $html .= '<ul>';
        foreach ($this->_nodes as $node)
        {
          $html .= $node;
        }
        $html .= '</ul>';
      }
      $html .= '</li>';
      return $html;
    }
  }

  public function getPathAsString()
  {
    $nodes = array();
    $obj = $this;

    do {
    	$nodes[] = $obj->getName();
    } while ($obj = $obj->getParent());

    return implode(' > ', array_reverse($nodes));
  }
}