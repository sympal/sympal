<?php
abstract class sfSympalMenu
{
  protected 
    $_name             = null,
    $_level            = null,
    $_parent           = null,
    $_requiresAuth     = null,
    $_requiresNoAuth   = null,
    $_debug            = true,
    $_showChildren     = true,
    $_current          = false,
    $_options          = array(),
    $_children         = array(),
    $_credentials      = array();

  public function __construct($name = null)
  {
    $this->_name = $name;
  }

  public function __toString()
  {
    try {
      return (string) $this->_renderChildren();
    } catch (Exception $e) {
      return $e->getMessage();
    }
  }

  public function debug($bool = null)
  {
    if (!is_null($bool))
    {
      $this->_debug = $bool;

      if ($this->hasChildren())
      {
        foreach ($this->_children as $child)
        {
          $child->debug($bool);
        }
      }
    }

    return $this->_debug;
  }

  public function requiresAuth($bool = null)
  {
    if (!is_null($bool))
    {
      $this->_requiresAuth = $bool;
    }

    return $this->_requiresAuth;
  }

  public function requiresNoAuth($bool = null)
  {
    if (!is_null($bool))
    {
      $this->_requiresNoAuth = $bool;
    }

    return $this->_requiresNoAuth;
  }

  public function setCredentials($credentials)
  {
    $this->_credentials = is_string($credentials) ? explode(',', $credentials):(array) $credentials;
  }

  public function getCredentials()
  {
    return $this->_credentials;
  }

  public function hasCredentials()
  {
    return !empty($this->_credentials);
  }

  public function showChildren($bool = null)
  {
    if (!is_null($bool))
    {
      $this->_showChildren = $bool;
    }

    return $this->_showChildren;
  }

  public function checkUserAccess(User $user = null)
  {
    if (!sfContext::hasInstance())
    {
      return true;
    }

    if (is_null($user))
    {
      $user = sfContext::getInstance()->getUser();
    }

    if (($user->isAuthenticated() && $this->requiresNoAuth()) || (!$user->isAuthenticated() && $this->requiresAuth()))
    {
      return false;
    }

    return $user->hasCredential($this->_credentials);
  }

  public function setLevel($level)
  {
    $this->_level = $level;
  }

  public function getLevel()
  {
    if (is_null($this->_level))
    {
      $count = -2;
      $obj = $this;

      do {
      	$count++;
      } while ($obj = $obj->getParent());

      $this->_level = $count;
    }

    return $this->_level;
  }

  public function getParent()
  {
    return $this->_parent;
  }

  public function setParent(sfSympalMenu $parent)
  {
    return $this->_parent = $parent;
  }

  public function getName()
  {
    return $this->_name;
  }

  public function setName($name)
  {
    $this->_name = $name;
  }

  public function getChildren()
  {
    return $this->_children;
  }

  public function setChildren(array $children)
  {
    $this->_children = $children;
  }

  public function addChild($child, $route = null, $options = array())
  {
    if (!$child instanceof sfSympalMenu)
    {
      $class = get_class($this);
      $child = new $class($child, $route, $options);
    }

    $child->setParent($this);
    $child->showChildren($this->showChildren());
    $child->debug($this->debug());

    $this->_children[$child->getName()] = $child;

    return $child;
  }

  public function getFirstChild()
  {
    return current($this->_children);
  }

  public function getLastChild()
  {
    return end($this->_children);
  }

  public function getChild($name)
  {
    if (!isset($this->_children[$name]))
    {
      $this->addChild($name);
    }

    return $this->_children[$name];
  }

  public function hasChildren()
  {
    return !empty($this->_children);
  }

  protected function _renderChildren()
  {
    if ($this->hasChildren() && $this->checkUserAccess())
    {
      $html  = '<ul>';

      foreach ($this->_children as $child)
      {
        $html .= $child->_render();
      }

      $html .= '</ul>';

      return $html;
    }
  }

  protected function _render()
  {
    if ($this->checkUserAccess())
    {
      $html = '<li class="'.Doctrine_Inflector::urlize($this->getName()).'" '.($this->isCurrent() ? ' id="current"':null).'>';
      if ($this->_route)
      {
        $options = $this->getOptions();
        if  ($this->isCurrent())
        {
          $options['class'] = 'current';
        }
        sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
        $menuItem = $this->getMenuItem();

        $html .= link_to($this->getLabel(), $this->getRoute(), $options);
      } else {
        $html .= $this->getLabel();
      }
      if ($this->hasChildren() && $this->showChildren())
      {
        $html .= '<ul>';
        foreach ($this->_children as $child)
        {
          $html .= $child->_render();
        }
        $html .= '</ul>';
      }
      $html .= '</li>';
      return $html;
    }
  }

  public function isCurrent($bool = null)
  {
    if (!is_null($bool))
    {
      $this->_current = $bool;
    }
    return $this->_current;
  }

  public function getLabel()
  {
    return (is_array($this->_options) && isset($this->_options['label'])) ? $this->_options['label']:$this->_name;
  }

  public function setLabel($label)
  {
    $this->_options['label'] = $label;
  }

  public function getPathAsString()
  {
    $children = array();
    $obj = $this;

    do {
    	$children[] = $obj->getName();
    } while ($obj = $obj->getParent());

    return implode(' > ', array_reverse($children));
  }
}