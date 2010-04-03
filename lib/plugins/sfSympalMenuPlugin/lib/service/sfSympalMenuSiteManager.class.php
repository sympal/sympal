<?php

/**
 * Service class that manages the sfSympalMenuSite instances
 * 
 * @package     sfSympalMenuPlugin
 * @subpackage  service
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-03-28
 * @version     svn:$Id$ $Author$
 */
class sfSympalMenuSiteManager
{
  protected
    $_cacheManager;
  
  protected
    $_currentMenuItem;
  
  protected
    $_menus = array(),
    $_menuItems = array(),
    $_rootSlugs = array(),
    $_rootMenuItems = array(),
    $_hierarchies = array(),
    $_initialized = false;

  /**
   * Class constructor
   * 
   * Takes an optional cache dependency - used to cache the menu
   * 
   * @param sfSympalCacheManager $cacheManager The cache manager service
   */
  public function __construct(sfSympalCacheManager $cacheManager = null)
  {
    $this->_cacheManager = $cacheManager;
    
    if ($cache = $this->_getCache())
    {
      $cachedRootSlugs = $cache->get('SYMPAL_MENU_ROOT_SLUGS');
      if (is_array($cachedRootSlugs))
      {
        $this->_rootSlugs = $cachedRootSlugs;
      } else {
        $this->initialize();
      }
    }
  }

  /**
   * @deprecated
   */
  public static function getInstance()
  {
    throw new sfException("Method is deprecated. Use ->getService('menu_manager') on sfSympalContext");
  }

  public function getMenus()
  {
    $menus = array();
    foreach ($this->_rootSlugs as $slug)
    {
      $menus[$slug] = $this->getMenu($slug);
    }
    return $menus;
  }

  public function findMenuItemByUri($uri, $menu = null)
  {
    if (is_null($menu))
    {
      foreach ($this->getMenus() as $menu)
      {
        if ($found = $this->findMenuItemByUri($uri, $menu))
        {
          return $found;
        }
      }
    } else {
      if ($menu->getUrl(array('absolute' => true)) === $uri)
      {
        return $menu->getMenuItem();
      }
      foreach ($menu->getChildren() as $child)
      {
        if ($found = $this->findMenuItemByUri($uri, $child))
        {
          return $found;
        }
      }
    }
  }

  public function clear()
  {
    $this->_menuItems = array();
    $this->_rootSlugs = array();
    $this->_rootMenuItems = array();
    $this->_hierarchies = array();
    $this->_initialized = false;
  }

  public function refresh()
  {
    $this->clear();
    $this->initialize();
  }

  public static function getMenu($name, $showChildren = null, $class = null)
  {
    return sfSympalContext::getInstance()->getService('menu_manager')->_getMenu($name, $showChildren, $class);
  }

  /**
   * @return sfSympalCacheManager
   */
  protected function _getCache()
  {
    return sfSympalConfig::get('menu_cache', 'enabled', true) ? $this->_cacheManager : false;
  }

  /**
   * Removes a menu item from the cache
   * 
   * @param string $key The cache key for the menu to remove.
   */
  public function clearCache($key)
  {
    $this->_getCache()->remove($key);
  }

  protected function _getMenu($name, $showChildren = null, $class = null)
  {
    if ($showChildren === null)
    {
      $showChildren = true;
    }
    if (is_scalar($name) && isset($this->_rootSlugs[$name]))
    {
      $name = $this->_rootSlugs[$name];
    }

    if (!$name)
    {
      return false;
    }
    
    $showChildren = (bool) $showChildren;

    $cacheKey = 'SYMPAL_MENU_'.md5((string) $name.var_export($showChildren, true).$class);
    if (isset($this->_menus[$cacheKey]))
    {
      return $this->_menus[$cacheKey];
    }

    $cache = $this->_getCache();
    if ($cache && $cache->has($cacheKey))
    {
      $menu = $cache->get($cacheKey);
    } else {
      $this->initialize();
      $menu = $this->_buildMenu($name, $class);
      if ($cache)
      {
        $cache->set($cacheKey, $menu);
      }
    }

    $this->_menus[$cacheKey] = $menu;

    if ($menu)
    {
      $menu->callRecursively('showChildren', $showChildren);

      $menu->setCacheKey($cacheKey);
    }

    return $menu;
  }

  protected function _buildMenu($name, $class)
  {
    if ($name instanceof sfSympalMenuItem)
    {
      $menuItem = $name;
      $rootId = $name['root_id'];
      $name = (string) $name;
    } else {
      $rootId = array_search($name, (array) $this->_rootSlugs);
    }

    if (!$rootId)
    {
      return false;
    }

    $rootMenuItem = $this->_rootMenuItems[$rootId];

    $class = $class ? $class:sfSympalConfig::get('menu_class', null, 'sfSympalMenuSite');
    $menu = new $class($name);
    $menu->setMenuItem($rootMenuItem);

    $hierarchy = $this->_hierarchies[$rootId];
    $this->_buildMenuHierarchy($hierarchy, $menu);

    if (isset($menuItem))
    {
      return $menu->getMenuItemSubMenu($menu->findMenuItem($menuItem)->getTopLevelParent()->getMenuItem());
    } else {
      return $menu;
    }
  }

  public static function split($menu, $max, $split = false)
  {
    $count = 0;
    $primaryChildren = array();
    $primary = clone $menu;

    if ($split)
    {
      $secondaryChildren = array();
      $secondary = clone $menu;
      $secondary->setName('secondary');
    }

    foreach ($menu->getChildren() as $child)
    {
      if (!$child->checkUserAccess())
      {
        continue;
      }

      $count++;
      if ($count > $max)
      {
        if ($split)
        {
          $secondaryChildren[] = $child;
          continue;
        } else {
          break;
        }
      }
      $primaryChildren[] = $child;
    }

    $primary->setChildren($primaryChildren);

    if ($split)
    {
      $secondary->setChildren($secondaryChildren);

      return array('primary' => $primary, 'secondary' => $secondary);
    } else {
      return $primary;
    }
  }

  public function initialize()
  {
    if (!$this->_initialized)
    {
      $this->_menuItems = Doctrine_Core::getTable('sfSympalMenuItem')->getMenuHierarchies();

      if (count($this->_menuItems) > 0)
      {
        foreach ($this->_menuItems as $menuItem)
        {
          $this->_rootSlugs[$menuItem['root_id']] = $menuItem['slug'];
          $this->_rootMenuItems[$menuItem['root_id']] = $menuItem;
          $this->_hierarchies[$menuItem['root_id']] = $menuItem['__children'];
        }
      }

      if ($cache = $this->_getCache())
      {
        $cache->set('SYMPAL_MENU_ROOT_SLUGS', $this->_rootSlugs);
      }

      // Mark the process as done so it is cached
      $this->_initialized = true;
    }
  }

  protected function _buildMenuHierarchy($hierarchy, $menu)
  {
    $user = sfContext::getInstance()->getUser();

    foreach ($hierarchy as $menuItem)
    {
      $new = $menu->addChild($menuItem->getSlug());
      $new->setName($menuItem->getName());
      $new->setMenuItem($menuItem);

      if (isset($menuItem['__children']) && !empty($menuItem['__children']))
      {
        $this->_buildMenuHierarchy($menuItem['__children'], $new);
      }
    }
  }

  /**
   * Returns the current menu item, if there is one
   * 
   * @return sfSympalMenuItem
   */
  public function getCurrentMenuItem()
  {
    return $this->_currentMenuItem;
  }

  /**
   * Sets the current menu item
   * 
   * @sfSympalMenuItem $menuItem The menu item that represents this url
   */
  public function setCurrentMenuItem(sfSympalMenuItem $menuItem)
  {
    $this->_currentMenuItem = $menuItem;
  }

  /**
   * Listens to the sympal.content.set_content event
   * 
   * This is notified when the current content is set. If the content is
   * connected to a menu item, it'll be set as current
   */
  public function listenContentSetContent(sfEvent $event)
  {
    if ($menuItem = $event->getSubject()->getMenuItem())
    {
      $this->setCurrentMenuItem($menuItem);
    }
  }
}