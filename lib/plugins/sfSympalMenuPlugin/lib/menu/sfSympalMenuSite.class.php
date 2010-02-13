<?php

class sfSympalMenuSite extends sfSympalMenu
{
  protected 
    $_contentRouteObject = null,
    $_menuItemArray = null,
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
    if ($cache = sfSympalConfiguration::getActive()->getCache())
    {
      return $cache->remove($this->_cacheKey);
    }
  }

  public function findMenuItem($menuItem)
  {
    if ($this->_menuItemArray['id'] == $menuItem['id'])
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

  public function getMenuItemArray()
  {
    return $this->_menuItemArray;
  }

  public function getBreadcrumbsArray($subItem = null)
  {
    $breadcrumbs = array();
    $obj = $this;

    if ($subItem)
    {
      if ($subItem instanceof sfSympalContent && $this->_menuItemArray['content_id'] == $subItem->id)
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
      $array['html_attributes'] = _parse_attributes($menuItem->getHtmlAttributes());
      unset($array['__children']);

      if (sfSympalConfig::isI18nEnabled('sfSympalMenuItem'))
      {
        $array['Translation'] = $menuItem->Translation->toArray(false);
      }

      return $array;
    }
    return $menuItem;
  }

  public function getLabel()
  {
    $label = null;
    if (sfSympalConfig::isI18nEnabled('sfSympalMenuItem'))
    {
      $culture = sfContext::getInstance()->getUser()->getCulture();
      if (isset($this->_menuItemArray['Translation'][$culture]['label']))
      {
        $label = $this->_menuItemArray['Translation'][$culture]['label'];
      }
      if (!$label && isset($this->_menuItemArray['Translation'][sfConfig::get('sf_default_culture')]['label']))
      {
        $label = $this->_menuItemArray['Translation'][sfConfig::get('sf_default_culture')]['label'];
      }
    } else {
      if (isset($this->_menuItemArray['label']))
      {
        $label = $this->_menuItemArray['label'];
      }
    }
    if (!$label)
    {
      $label = parent::getLabel();
    }
    return $label;
  }

  public function getRoute()
  {
    if ($this->_contentRouteObject)
    {
      return $this->_contentRouteObject->getRoute();
    } else {
      return parent::getRoute();
    }
  }

  public function setMenuItem($menuItem)
  {
    if ($content = $menuItem->getRelatedContent())
    {
      $this->_contentRouteObject = $content->getContentRouteObject();
    }
    $this->_menuItemArray = $this->_prepareMenuItem($menuItem);
    $this->setRoute($this->_menuItemArray['item_route']);
    $this->requiresAuth($this->_menuItemArray['requires_auth']);
    $this->requiresNoAuth($this->_menuItemArray['requires_no_auth']);
    $this->setCredentials($this->_menuItemArray['all_permissions']);
    $this->setOptions($this->_menuItemArray['html_attributes']);

    // If not published yet then you must have certain credentials
    $datePublished = strtotime($this->_menuItemArray['date_published']);
    if (!$datePublished || $datePublished > time())
    {
      $this->setCredentials(array('ManageContent'));
    }

    $this->setLevel($this->_menuItemArray['level']);
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
      if ($child->_menuItemArray['id'] == $menuItem['id'] && $child->getChildren())
      {
        $result = $child;
      } else if ($n = $child->getMenuItemSubMenu($menuItem)) {
        $result = $n;
      }

      if (isset($result))
      {
        $class = $result->getParent() ? get_class($result->getParent()):get_class($result);
        $instance = new $class($child->getName());
        $instance->setChildren($result->getChildren());

        return $instance;
      }
    }
  }

  public function isCurrent($bool = null)
  {
    $currentMenuItem = sfSympalContext::getInstance()->getCurrentMenuItem();

    if ($currentMenuItem && $currentMenuItem->exists())
    {
      return $this->_menuItemArray['id'] == $currentMenuItem['id'];
    } else {
      return false;
    }
  }

  public function isCurrentAncestor()
  {
    $menuItem = sfSympalContext::getInstance()->getCurrentMenuItem();
    if ($menuItem && $this->_menuItemArray)
    {
      $this->_currentObject = $this->findMenuItem($menuItem);
      return parent::isCurrentAncestor();
    }

    return false;
  }

  public function getMenuItem()
  {
    return Doctrine_Core::getTable('sfSympalMenuItem')->find($this->_menuItemArray['id']);
  }
}