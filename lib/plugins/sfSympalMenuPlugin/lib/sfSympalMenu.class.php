<?php
abstract class sfSympalMenu
{
  protected 
    $_level  = null,
    $_parent = null,
    $_name   = array(),
    $_nodes  = array(),
    $_requiresAuth = null,
    $_requiresNoAuth = null,
    $_credentials = array(),
    $_recursiveOutput = true,
    $_menuItemDropDown = true;

  public function __construct($name = null)
  {
    $this->_name = $name;
  }

  public function showMenuItemDropDown($bool = null)
  {
    if (!is_null($bool))
    {
      $this->_menuItemDropDown = $bool;
    }
    return $this->_menuItemDropDown;
  }

  public function __toString()
  {
    try {
      return (string) $this->_render();
    } catch (Exception $e) {
      return $e->getMessage();
    }
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

  public function isRecursiveOutput($bool = null)
  {
    if (!is_null($bool))
    {
      $this->_recursiveOutput = $bool;
    }

    return $this->_recursiveOutput;
  }

  public function checkUserAccess(sfGuardUser $user = null)
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

  public function getNodes()
  {
    return $this->_nodes;
  }

  public function setNodes(array $nodes)
  {
    $this->_nodes = $nodes;
  }

  public function addNode($node, $route = null, $options = array())
  {
    if (!$node instanceof sfSympalMenuNode)
    {
      $class = get_class($this);

      if (!strstr($class, 'Node'))
      {
        $class = $class.'Node';
        if (!class_exists($class))
        {
          throw new sfException('You must create a class named "'.$class.'"');
        }
      }

      $node = new $class($node, $route, $options);
    }

    $node->setParent($this);
    $node->isRecursiveOutput($this->isRecursiveOutput());
    $node->showMenuItemDropDown($this->showMenuItemDropDown());

    $this->_nodes[$node->getName()] = $node;

    return $node;
  }

  public function getLastNode()
  {
    return end($this->_nodes);
  }

  public function getNode($name)
  {
    if (!isset($this->_nodes[$name]))
    {
      $this->addNode($name);
    }
    return $this->_nodes[$name];
  }

  public function hasNodes()
  {
    return !empty($this->_nodes);
  }

  public function _render()
  {
    if ($this->hasNodes() && $this->checkUserAccess())
    {
      $html  = '<ul>';

      foreach ($this->_nodes as $node)
      {
        $html .= $node;
      }

      $html .= '</ul>';

      return $html;
    }
  }

  protected $_menuItem;

  public function getMenuItem()
  {
    return $this->_menuItem;
  }

  public function setMenuItem($menuItem)
  {
    $this->_menuItem = $menuItem;

    $this->requiresAuth($menuItem->requires_auth);
    $this->requiresNoAuth($menuItem->requires_no_auth);
    $this->setCredentials($menuItem->getAllPermissions());
    $currentMenuItem = sfSympalTools::getCurrentMenuItem();
    if ($currentMenuItem && $currentMenuItem->exists() && $this instanceof sfSympalMenuNode)
    {
      $this->isCurrent($menuItem->id == $currentMenuItem->id);
    }
    $this->setLevel($menuItem->level);
  }

  public function getMenuItemSubMenu($menuItem)
  {
    foreach ($this->_nodes as $node)
    {
      if ($node->getMenuItem()->id == $menuItem->id && $node->getNodes())
      {
        $result = $node;
      } else if ($n = $node->getMenuItemSubMenu($menuItem)) {
        $result = $n;
      }
      if (isset($result))
      {
        $class = get_class($result->getParent());
        $instance = new $class();
        $instance->setMenuItem($menuItem);
        $instance->setNodes($result->getNodes());
        return $instance;
      }
    }
  }
}