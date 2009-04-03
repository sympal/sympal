<?php

class sfSympalMenuSiteManager
{
  protected
    $_menuItems = array(),
    $_rootNames = array(),
    $_rootMenuItems = array(),
    $_hierarchies = array(),
    $_menus = array(),
    $_menuHierarchiesBuilt = false;

  protected static $_instance;

  public static function getInstance()
  {
    if (!self::$_instance)
    {
      self::$_instance = new sfSympalMenuSiteManager();
    }
    return self::$_instance;
  }

  public function getHierarchies()
  {
    $this->_buildMenuHierarchies();
    return $this->_hierarchies;
  }

  public function refresh()
  {
    $this->_menuItems = array();
    $this->_rootNames = array();
    $this->_rootMenuItems = array();
    $this->_hierarchies = array();
    $this->_menus = array();
    $this->_menuHierarchiesBuilt = false;
  }

  public static function getMenu($name, $showChildren = true, $class = null)
  {
    return self::getInstance()->_getMenu($name, $showChildren, $class);
  }

  protected function _getMenu($name, $showChildren = true, $class = null)
  {
    if (!$name)
    {
      return false;
    }

    $this->_buildMenuHierarchies();

    if ($name instanceof MenuItem)
    {
      $menuItem = $name;
      $name = $this->_rootNames[$name['root_id']];
    }

    if (!isset($this->_menus[$name]))
    {
      $rootId = array_search($name, $this->_rootNames);
      $rootMenuItem = $this->_rootMenuItems[$rootId];

      $class = $class ? $class:'sfSympalMenuSite';
      $menu = new $class($name);
      $menu->setMenuItem($rootMenuItem);
      $menu->showChildren($showChildren);

      if (!$rootId)
      {
        return false;
      }
      $hierarchy = $this->_hierarchies[$rootId];
      $this->_buildMenuHierarchy($hierarchy, $menu);
    } else {
      $menu = $this->_menus[$name];
      $menu->showChildren($showChildren);
    }

    if (isset($menuItem))
    {
      return $menu->getMenuItemSubMenu($menuItem);
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

  protected function _buildMenuHierarchies()
  {
    if (!$this->_menuHierarchiesBuilt)
    {
      // Query for the all menu items
      $q = Doctrine_Query::create()
        ->from('MenuItem m INDEXBY m.id')
        ->leftJoin('m.Groups g')
        ->leftJoin('g.Permissions gp')
        ->leftJoin('m.Permissions mp')
        ->leftJoin('m.RelatedContent c')
        ->leftJoin('c.Type ct')
        ->leftJoin('m.ContentType ct2')
        ->leftJoin('m.Site s')
        ->orderBy('m.root_id, m.lft ASC');

      if (sfSympalConfig::get('I18n', 'MenuItem'))
      {
        $q->leftJoin('m.Translation t');
      }

      $this->_menuItems = $q->execute();

      // Build array of root tree names, root menu items.
      // Also build collection of sub arrays for each tree
      // so we can build the hierarchy for each tree
      $trees = array();
      $this->_rootNames = array();
      foreach ($this->_menuItems as $menuItem)
      {
        if (!isset($trees[$menuItem['root_id']]))
        {
          $trees[$menuItem['root_id']] = new Doctrine_Collection('MenuItem');
        }
  
        if ($menuItem['level'] == 0)
        {
          $this->_rootMenuItems[$menuItem['root_id']] = $menuItem;
          $this->_rootNames[$menuItem['root_id']] = $menuItem['name'];
          continue;
        }

        $trees[$menuItem['root_id']][] = $menuItem;
      }

      // Build the hierarchies from the flat array of menu items
      $this->_hierarchies = array();
      foreach ($trees as $rootId => $tree)
      {
        $this->_hierarchies[$rootId] = self::toHierarchy($tree->toArray());
      }

      // Mark the process as done so it is cached
      $this->_menuHierarchiesBuilt = true;
    }
  }

  protected function _buildMenuHierarchy($hierarchy, $menu)
  {
    $user = sfContext::getInstance()->getUser();

    foreach ($hierarchy as $child)
    {
      $menuItem = $this->_menuItems[$child['id']];
      $new = $menu->addChild($menuItem->getLabel(), $menuItem->getItemRoute());
      $new->setMenuItem($menuItem);

      if (isset($child['__children']) && !empty($child['__children']))
      {
        $this->_buildMenuHierarchy($child['__children'], $new);
      }
    }
  }

  public static function toHierarchy($collection)
  {
  	// Trees mapped
  	$trees = array();
  	$l = 0;

  	if (count($collection) > 0) {
  		// Node Stack. Used to help building the hierarchy
  		$stack = array();

  		foreach ($collection as $child) {
  			$item = $child;
  			$item['__children'] = array();

  			// Number of stack items
  			$l = count($stack);

  			// Check if we're dealing with different levels
  			while($l > 0 && $stack[$l - 1]['level'] >= $item['level']) {
  				array_pop($stack);
  				$l--;
  			}

  			// Stack is empty (we are inspecting the root)
  			if ($l == 0) {
  				// Assigning the root child
  				$i = count($trees);
  				$trees[$i] = $item;
  				$stack[] = & $trees[$i];
  			} else {
  				// Add child to parent
  				$i = count($stack[$l - 1]['__children']);
  				$stack[$l - 1]['__children'][$i] = $item;
  				$stack[] = & $stack[$l - 1]['__children'][$i];
  			}
  		}
  	}

  	return $trees;
  }
}