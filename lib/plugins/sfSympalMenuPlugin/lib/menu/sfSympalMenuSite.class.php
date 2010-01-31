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
    if ($cache = sfSympalConfiguration::getActive()->getCache())
    {
      return $cache->remove($this->_cacheKey);
    }
  }

  public function findMenuItem($menuItem)
  {
    if ($this->_menuItem['id'] == $menuItem['id'])
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
      if (isset($this->_menuItem['Translation'][$culture]['label']))
      {
        $label = $this->_menuItem['Translation'][$culture]['label'];
      }
      if (!$label && isset($this->_menuItem['Translation'][sfConfig::get('sf_default_culture')]['label']))
      {
        $label = $this->_menuItem['Translation'][sfConfig::get('sf_default_culture')]['label'];
      }
    } else {
      if (isset($this->_menuItem['label']))
      {
        $label = $this->_menuItem['label'];
      }
    }
    if (!$label)
    {
      $label = parent::getLabel();
    }
    return $label;
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

  public function isCurrent($bool = null)
  {
    $currentMenuItem = sfSympalContext::getInstance()->getCurrentMenuItem();

    if ($currentMenuItem && $currentMenuItem->exists())
    {
      return $this->_menuItem['id'] == $currentMenuItem['id'];
    } else {
      return false;
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

  public function getDoctrineMenuItem()
  {
    return Doctrine_Core::getTable('sfSympalMenuItem')->find($this->_menuItem['id']);
  }
}