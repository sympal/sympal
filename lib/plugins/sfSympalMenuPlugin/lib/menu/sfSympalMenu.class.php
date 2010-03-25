<?php

/**
 * Base menu item in sympal
 * 
 * @package     sfSympalMenuPlugin
 * @subpackage  menu
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @version     svn:$Id$ $Author$
 */
class sfSympalMenu implements ArrayAccess, Countable, IteratorAggregate
{
  protected
    $_name             = null,
    $_ulClass          = null,
    $_liClass          = null,
    $_route            = null,
    $_level            = null,
    $_num              = null,
    $_parent           = null,
    $_root             = null,
    $_requiresAuth     = null,
    $_requiresNoAuth   = null,
    $_showChildren     = true,
    $_current          = null,
    $_currentObject    = null,
    $_options          = array(),
    $_children         = array(),
    $_credentials      = array();

  public function __construct($name, $route = null, $options = array())
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Tag', 'Url'));

    $this->_name = $name;
    $this->_route = $route;
    $this->_options = $options;
  }

  public function count()
  {
    return count($this->_children);
  }

  public function getIterator()
  {
    return new ArrayObject($this->_children);
  }

  public function add($value)
  {
    return $this->addChild($value)->setLabel($value);
  }

  public function current()
  {
    return current($this->_children);
  }

  public function next()
  {
    return next($this->_children);
  }

  public function key()
  {
    return key($this->_children);
  }

  public function valid()
  {
    return $this->current() !== false;
  }

  public function rewind()
  {
    return reset($this->_children);
  }

  public function offsetExists($name)
  {
    return isset($this->_children[$name]);
  }

  public function offsetGet($name)
  {
    return $this->getChild($name);
  }

  public function offsetSet($name, $value)
  {
    return $this->addChild($name)->setLabel($value);
  }

  public function offsetUnset($name)
  {
    unset($this->_children[$name]);
  }

  public function getUlClass()
  {
    return $this->_ulClass;
  }

  public function setUlClass($ulClass)
  {
    $this->_ulClass = $ulClass;
  }

  public function getLiClass()
  {
    return $this->_liClass;
  }

  public function setLiClass($liClass)
  {
    $this->_liClass = $liClass;
  }

  public function getRoute()
  {
    return $this->_route;
  }

  /**
   * Generates the url to this menu item based on the route
   * 
   * In case the route is totally invalid, this catches the exception
   * and sends to the raw string
   * 
   * @TODO Find a more explicit way to log if the route is invalid
   * 
   * @param array $options Options to pass to the url_for method
   */
  public function getUrl(array $options = array())
  {
    try
    {
      return url_for($this->getRoute(), $options);
    }
    catch (sfConfigurationException $e)
    {
      sfApplicationConfiguration::getActive()->getEventDispatcher()->notify(
        new sfEvent($this, 'application.log', array(
          sprintf('Cannot generate a menu url for "%s"', $this->getRoute())
        ))
      );
      
      return $this->getRoute();
    }
  }

  public function setRoute($route)
  {
    $this->_route = $route;

    return $this;
  }

  public function getOptions()
  {
    return $this->_options;
  }

  public function setOptions($options)
  {
    $this->_options = $options;

    return $this;
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

    return $this;
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

    return $this;
  }

  public function getCredentials()
  {
    return $this->_credentials;
  }

  public function hasCredentials()
  {
    $credentials = $this->getCredentials();
    return !empty($credentials);
  }
  
  /**
   * Returns and optionally sets whether or not this menu item should
   * show its children. If the $bool argument is passed, the _showChildren
   * property will be set
   * 
   * @param boolean $bool Whether to show children or not
   */
  public function showChildren($bool = null)
  {
    if ($bool !== null)
    {
      $this->_showChildren = (bool) $bool;
    }

    return $this->_showChildren;
  }

  public function checkUserAccess(sfUser $user = null)
  {
    if (!sfContext::hasInstance())
    {
      return true;
    }

    if (is_null($user))
    {
      $user = sfContext::getInstance()->getUser();
    }

    if ($user->isAuthenticated() && $this->requiresNoAuth())
    {
      return false;
    }

    if (!$user->isAuthenticated() && $this->requiresAuth())
    {
      return false;
    }

    return $user->hasCredential($this->getCredentials());
  }

  public function setLevel($level)
  {
    $this->_level = $level;

    return $this;
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

  public function getRoot()
  {
    if (is_null($this->_root))
    {
      $obj = $this;
      do {
        $found = $obj;
      } while ($obj = $obj->getParent());
      $this->_root = $found;
    }
    return $this->_root;
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

    return $this;
  }

  public function getChildren()
  {
    return $this->_children;
  }

  public function setChildren(array $children)
  {
    $this->_children = $children;

    return $this;
  }

  public function getNum()
  {
    return $this->_num;
  }

  public function setNum($num)
  {
    $this->_num = $num;
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
    $child->setNum($this->count() + 1);

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
  
  /**
   * Returns whether or not this menu items has viewable children
   * 
   * This menu MAY have children, but this will return false if the current
   * user does not have access to vew any of those items
   * 
   * @return boolean;
   */
  public function hasChildren()
  {
    foreach ($this->_children as $child)
    {
      if ($child->checkUserAccess())
      {
        return true;
      }
    }
    
    return false;
  }

  public function __toString()
  {
    try {
      return (string) $this->render();
    } catch (Exception $e) {
      return $e->getMessage();
    }
  }

  public function render()
  {
    if ($this->checkUserAccess() && $this->hasChildren())
    {
      $id = Doctrine_Inflector::urlize($this->getName().'-menu');
      $html = '<ul'.($this->_ulClass ? ' class="'.$this->_ulClass.'"' : null).' id="'.$id.'">';
      $html .= $this->renderChildren();
      $html .= '</ul>';
      return $html;
    }
  }

  public function renderChildren()
  {
    $html = '';
    foreach ($this->_children as $child)
    {
      $html .= $child->renderChild();
    }
    return $html;
  }

  public function renderChild()
  {
    if ($this->checkUserAccess())
    {
      $class = array();
      if ($this->isCurrent() || $this->isCurrentAncestor())
      {
        $class[] = 'current';
      }
      if ($this->isFirst())
      {
        $class[] = 'first';
      }
      if ($this->isLast())
      {
        $class[] = 'last';
      }
      if ($this->_liClass)
      {
        $class[] = $this->_liClass;
      }
      $id = Doctrine_Inflector::urlize($this->getRoot()->getName().'-'.$this->getName());
      $html = '<li id="'.$id.'"'.(!empty($class) ? ' class="'.implode(' ', $class).'"':null).'>';
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
    if ($this->_route)
    {
      $html = $this->renderLink();
    } else {
      $html = $this->renderLabel();
    }
    return $html;
  }

  public function renderLink()
  {
    if ($route = $this->getRoute())
    {
      $options = $this->getOptions();
      $currentAncestor = $this->isCurrentAncestor();
      if  ($this->isCurrent() || $currentAncestor)
      {
        if (!isset($options['class']))
        {
          $class = '';
        } else {
          $class = $options['class'];
        }
        $class .= ' current';
        if ($currentAncestor)
        {
          $class .= ' current_ancestor';
        }
        $options['class'] = $class;
      }
      
      $html = link_to($this->renderLabel(), $route, $options);
    } else {
      $html = $this->renderLabel();
    }
    return $html;
  }

  public function renderLabel()
  {
    return __($this->getLabel());
  }

  public function getBreadcrumbsArray($subItem = null)
  {
    $breadcrumbs = array();
    $obj = $this;

    if ($subItem)
    {
      if (!is_array($subItem))
      {
        $subItem = array((string) $subItem => null);
      }
      $subItem = array_reverse($subItem);
      foreach ($subItem as $key => $value)
      {
        if (is_numeric($key))
        {
          $key = $value;
          $value = null;
        }
        $breadcrumbs[(string) $key] = $value;
      }
    }

    do {
      $label = __($obj->getLabel());
    	$breadcrumbs[$label] = $obj->getRoute();
    } while ($obj = $obj->getParent());

    return count($breadcrumbs) > 1 ? array_reverse($breadcrumbs):array();
  }

  public function getBreadcrumbs($subItem = null)
  {
    return sfSympalMenuBreadcrumbs::generate($this->getBreadcrumbsArray($subItem));
  }

  public function getCurrent()
  {
    if ($this->isCurrent())
    {
      return $this;
    }
    foreach ($this->_children as $child)
    {
      if ($current = $child->getCurrent())
      {
        return $current;
      }
    }
    return false;
  }

  public function isCurrent($bool = null)
  {
    if (is_null($this->_current))
    {
      if ($this->getUrl(array('absolute' => true)) == sfContext::getInstance()->getRequest()->getUri())
      {
        $this->_current = true;
        $this->getRoot()->setCurrentObject($this);
        $this->_current = true;
      }
    }

    if (!is_null($bool))
    {
      $this->_current = $bool;
    }

    return $this->_current;
  }

  public function setCurrentObject(sfSympalMenu $currentObject)
  {
    $this->_currentObject = $currentObject;
  }

  public function isCurrentAncestor()
  {
    $ret = false;
    if ($currentObject = $this->_currentObject)
    {
      while ($currentObject->getLevel() != 0)
      {
        if ($this->getRoute() == $currentObject->getRoute() && $this->getLabel() == $currentObject->getLabel())
        {
          $ret = true;

          break;
        }

        $ret = false;
        $currentObject = $currentObject->getParent();
      }
    }
    else
    {
      $ret = false;
    }

    return $ret;
  }

  public function getLabel()
  {
    return (is_array($this->_options) && isset($this->_options['label'])) ? $this->_options['label']:$this->_name;
  }

  public function setLabel($label)
  {
    $this->_options['label'] = $label;

    return $this;
  }

  public function getPathAsString()
  {
    $children = array();
    $obj = $this;

    do {
    	$children[] = __($obj->getLabel());
    } while ($obj = $obj->getParent());

    return implode(' > ', array_reverse($children));
  }

  public function callRecursively()
  {
    $args = func_get_args();
    $arguments = $args;
    unset($arguments[0]);

    call_user_func_array(array($this, $args[0]), $arguments);

    foreach ($this->_children as $child)
    {
      call_user_func_array(array($child, 'callRecursively'), $args);
    }

    return $this;
  }

  public function toArray()
  {
    $array = array();
    $array['name'] = $this->getName();
    $array['level'] = $this->getLevel();
    $array['is_current'] = $this->isCurrent();
    $array['options'] = $this->getOptions();
    foreach ($this->_children as $key => $child)
    {
      $array['children'][$key] = $child->toArray();
    }
    return $array;
  }

  public function fromArray($array)
  {
    $this->setName($array['name']);
    if (isset($array['level']))
    {
      $this->setLevel($array['level']);
    }
    if (isset($array['is_current']))
    {
      $this->isCurrent($array['is_current']);
    }
    if (isset($array['options']))
    {
      $this->setOptions($array['options']);
    }

    if (isset($array['children']))
    {
      foreach ($array['children'] as $name => $child)
      {
        $this->addChild($name)->fromArray($child);
      }
    }

    return $this;
  }

  public function isLast()
  {
    return $this->getNum() == $this->getParent()->count() ? true:false;
  }

  public function isFirst()
  {
    return $this->getNum() == 1 ? true:false;
  }

  public function __call($method, $arguments)
  {
    $class = get_class($this);
    $class = str_replace('sfSympalMenu', '', $class);
    $name  = $class ? 'sympal.menu.'.sfInflector::humanize($class):'sympal.menu';
    $name .= '.method_not_found';

    $event = sfProjectConfiguration::getActive()->getEventDispatcher()->notifyUntil(new sfEvent($this, $name, array('method' => $method, 'arguments' => $arguments)));
    if (!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }

    return $event->getReturnValue();
  }
}