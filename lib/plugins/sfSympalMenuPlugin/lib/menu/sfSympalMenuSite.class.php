<?php

class sfSympalMenuSite extends sfSympalMenu
{
  protected 
    $_menuItem = null,
    $_cacheKey = null;

  public function setCacheKey($cacheKey)
  {
    $this->_cacheKey = $cacheKey;
  }

  public function getCacheKey()
  {
    return $this->_cacheKey;
  }

  public function clearCache()
  {
    return sfSympalConfiguration::getActive()->getCache()->remove($this->_cacheKey);
  }

  public function findMenuItem(sfSympalMenuItem $menuItem)
  {
    if ($this->_menuItem['id'] == $menuItem->id)
    {
      return $this;
    }
    foreach ($this->_children as $child)
    {
      if ($i = $child->findMenuItem($menuItem))
      {
        return $i;
      }
    }
    return false;
  }

  public function getMenuItem()
  {
    return $this->_menuItem;
  }

  public function getPathAsString()
  {
    $path = array();
    $obj = $this;

    do {
    	$path[] = __($obj->_menuItem['label']);
    } while ($obj = $obj->getParent());

    return implode(' > ', array_reverse($path));
  }

  public function getBreadcrumbsArray($subItem = null)
  {
    $breadcrumbs = array();
    $obj = $this;

    if ($subItem)
    {
      if ($subItem instanceof sfSympalContent && $this->_menuItem['content_id'] == $subItem->id)
      {
        $subItem = array();
      }
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

  protected function _prepareMenuItem($menuItem)
  {
    if ($menuItem instanceof sfSympalMenuItem)
    {
      $array = $menuItem->toArray(false);
      $array['item_route'] = $menuItem->getItemRoute();
      $array['requires_auth'] = $menuItem->getRequiresAuth();
      $array['requires_no_auth'] = $menuItem->getRequiresNoAuth();
      $array['all_permissions'] = $menuItem->getAllPermissions();
      $array['level'] = $menuItem->getLevel();
      $array['date_published'] = $menuItem->getDatePublished();
      unset($array['__children']);
      return $array;
    }
    return $menuItem;
  }

  public function setMenuItem($menuItem)
  {
    $this->_menuItem = $this->_prepareMenuItem($menuItem);
    $this->setRoute($this->_menuItem['item_route']);
    $this->requiresAuth($this->_menuItem['requires_auth']);
    $this->requiresNoAuth($this->_menuItem['requires_no_auth']);
    $this->setCredentials($this->_menuItem['all_permissions']);

    // If not published yet then you must have certain credentials
    $datePublished = strtotime($this->_menuItem['date_published']);
    if (!$datePublished || $datePublished > time())
    {
      $this->setCredentials(array('ManageContent'));
    }

    $currentMenuItem = sfSympalContext::getInstance()->getCurrentMenuItem();

    if ($currentMenuItem && $currentMenuItem->exists())
    {
      $this->isCurrent($this->_menuItem['id'] == $currentMenuItem['id']);
    } else {
      $this->isCurrent(false);
    }

    $this->setLevel($this->_menuItem['level']);
  }

  public function getTopLevelParent()
  {
    $obj = $this;

    do {
    	if ($obj->getLevel() == 1)
    	{
    	  return $obj;
    	}
    } while ($obj = $obj->getParent());

    return $this;
  }

  public function getMenuItemSubMenu($menuItem)
  {
    foreach ($this->_children as $child)
    {
      if ($child->_menuItem['id'] == $menuItem['id'] && $child->getChildren())
      {
        $result = $child;
      } else if ($n = $child->getMenuItemSubMenu($menuItem)) {
        $result = $n;
      }

      if (isset($result))
      {
        $class = $result->getParent() ? get_class($result->getParent()):get_class($result);
        $instance = new $class($child->getName());
        $instance->setMenuItem($menuItem);
        $instance->setChildren($result->getChildren());

        return $instance;
      }
    }
  }

  public function isCurrentAncestor()
  {
    $menuItem = sfSympalContext::getInstance()->getCurrentMenuItem();
    if ($menuItem && $this->_menuItem)
    {
      $this->_currentObject = $this->findMenuItem($menuItem);
      return parent::isCurrentAncestor();
    }

    return false;
  }
}