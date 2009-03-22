<?php
class sfSympalMenuSite extends sfSympalMenu
{
  protected
    $_menuItems = array(),
    $_rootNames = array(),
    $_rootMenuItems = array(),
    $_hierarchies = array(),
    $_menus = array(),
    $_menuHierarchiesBuilt = false;

  protected static $_instance;

  public static function getMenu($name, $recursive = true, $max = null, $split = false)
  {
    if (!self::$_instance)
    {
      self::$_instance = new self();
    }
    return self::$_instance->_getMenu($name, $recursive, $max, $split);
  }

  protected function _getMenu($name, $recursive = true, $max = null, $split = false)
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

      $menu = new sfSympalMenuSite();
      $menu->setMenuItem($rootMenuItem);
      $menu->isRecursiveOutput($recursive);

      if (!$rootId)
      {
        return false;
      }
      $hierarchy = $this->_hierarchies[$rootId];
      $this->_buildMenuHierarchy($hierarchy, $menu);
    } else {
      $menu = $this->_menus[$name];
      $menu->isRecursiveOutput($recursive);
    }

    if (isset($menuItem))
    {
      $menu = $menu->getMenuItemSubMenu($menuItem);
    }
    if ($max)
    {
      return $this->_processMaxMenu($menu, $max, $split);
    } else {
      return $menu;
    }
  }

  protected function _processMaxMenu($menu, $max, $split)
  {
    $count = 0;
    $primaryNodes = array();
    $primary = new self();

    if ($split)
    {
      $secondaryNodes = array();
      $secondary = new self();
    }

    foreach ($menu->getNodes() as $node)
    {
      if (!$node->checkUserAccess())
      {
        continue;
      }

      $count++;
      if ($count > $max)
      {
        if ($split)
        {
          $secondaryNodes[] = $node;
          continue;
        } else {
          break;
        }
      }
      $primaryNodes[] = $node;
    }

    $primary->setNodes($primaryNodes);

    if ($split)
    {
      $secondary->setNodes($secondaryNodes);
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
        ->leftJoin('g.permissions gp')
        ->leftJoin('m.Permissions mp')
        ->leftJoin('m.RelatedEntity e')
        ->orderBy('m.root_id, m.lft ASC');

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
        $this->_hierarchies[$rootId] = $this->toHierarchy($tree->toArray());
      }

      // Mark the process as done so it is cached
      $this->_menuHierarchiesBuilt = true;
    }
  }

  protected function _buildMenuHierarchy($hierarchy, $menu)
  {
    $user = sfContext::getInstance()->getUser();

    foreach ($hierarchy as $node)
    {
      $menuItem = $this->_menuItems[$node['id']];
      $new = $menu->addNode($menuItem->getLabel(), $menuItem->getItemRoute());
      $new->setMenuItem($menuItem);

      if (isset($node['__children']) && !empty($node['__children']))
      {
        $this->_buildMenuHierarchy($node['__children'], $new);
      }
    }
  }

  public function toHierarchy($collection)
  {
  	// Trees mapped
  	$trees = array();
  	$l = 0;

  	if (count($collection) > 0) {
  		// Node Stack. Used to help building the hierarchy
  		$stack = array();

  		foreach ($collection as $node) {
  			$item = $node;
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
  				// Assigning the root node
  				$i = count($trees);
  				$trees[$i] = $item;
  				$stack[] = & $trees[$i];
  			} else {
  				// Add node to parent
  				$i = count($stack[$l - 1]['__children']);
  				$stack[$l - 1]['__children'][$i] = $item;
  				$stack[] = & $stack[$l - 1]['__children'][$i];
  			}
  		}
  	}

  	return $trees;
  }
}