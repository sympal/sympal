<?php

class sfSympalMenuSiteManager
{
  protected
    $_menus = array(),
    $_menuItems = array(),
    $_rootSlugs = array(),
    $_rootMenuItems = array(),
    $_hierarchies = array(),
    $_initialized = false;

  protected static $_instance;

  public function __construct()
  {
    $this->initialize();
  }

  public static function getInstance()
  {
    if (!self::$_instance)
    {
      $className = sfConfig::get('app_sympal_config_menu_manager_class');
      self::$_instance = new $className();
    }
    return self::$_instance;
  }

  public function getHierarchies()
  {
    $this->initialize();

    return $this->_hierarchies;
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

  public static function getMenu($name, $showChildren = true, $class = null)
  {
    return self::getInstance()->_getMenu($name, $showChildren, $class);
  }

  protected function _getMenu($name, $showChildren = true, $class = null)
  {
    $key = md5($name.var_export($showChildren, true).$class);
    if (isset($this->_menus[$key]))
    {
      return $this->_menus[$key];
    }

    if (!$name)
    {
      return false;
    }

    $this->initialize();

    if ($name instanceof sfSympalMenuItem)
    {
      $menuItem = $name;
      $name = $this->_rootSlugs[$name['root_id']];
    }

    $rootId = array_search($name, $this->_rootSlugs);

    if (!$rootId)
    {
      return false;
    }
    $rootMenuItem = $this->_rootMenuItems[$rootId];

    $class = $class ? $class:sfSympalConfig::get('menu_class', null, 'sfSympalMenuSite');
    $menu = new $class($name);
    $menu->setMenuItem($rootMenuItem);

    if (!$rootId)
    {
      return false;
    }
    $hierarchy = $this->_hierarchies[$rootId];
    $this->_buildMenuHierarchy($hierarchy, $menu);

    if (isset($menuItem))
    {
      $return = $menu->getMenuItemSubMenu($menu->findMenuItem($menuItem)->getTopLevelParent()->getMenuItem());
    } else {
      $return = $menu;
    }

    if ($return)
    {
      $return->callRecursively('showChildren', $showChildren);

      $this->_menus[$key] = $return;

      return $return;
    } else {
      return false;
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
      // Query for the all menu items
      $q = Doctrine_Query::create()
        ->from('sfSympalMenuItem m INDEXBY m.id')
        ->addSelect('m.*')
        ->leftJoin('m.Groups g')
        ->addSelect('g.id, g.name')
        ->leftJoin('g.Permissions gp')
        ->addSelect('gp.id, gp.name')
        ->leftJoin('m.RelatedContent c')
        ->addSelect('c.id, c.custom_path, c.slug')
        ->leftJoin('c.Type ct')
        ->addSelect('ct.id, ct.name, ct.default_path, ct.slug')
        ->innerJoin('m.Site s WITH s.slug = ?', sfSympalContext::getInstance()->getSiteSlug())
        ->orderBy('m.root_id, m.lft ASC');

      if (sfSympalConfig::isI18nEnabled('sfSympalContent'))
      {
        $q->leftJoin('c.Translation ctr');
        $q->addSelect('ctr.*');
      }

      if (sfSympalConfig::isI18nEnabled('sfSympalMenuItem'))
      {
        $q->leftJoin('m.Translation t');
        $q->addSelect('t.*');
      }

      $user = sfContext::getInstance()->getUser();
      if (!$user->isEditMode())
      {
        $expr = new Doctrine_Expression('NOW()');
        $q->andWhere('m.date_published <= '.$expr);
      }

      $this->_menuItems = $q->execute(array(), Doctrine_Core::HYDRATE_RECORD_HIERARCHY);

      foreach ($this->_menuItems as $menuItem)
      {
        $this->_rootSlugs[$menuItem['root_id']] = $menuItem['slug'];
        $this->_rootMenuItems[$menuItem['root_id']] = $menuItem;
        $this->_hierarchies[$menuItem['root_id']] = $menuItem['__children'];
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
      $new = $menu->addChild($menuItem->getLabel(), $menuItem->getItemRoute());
      $new->setMenuItem($menuItem);

      if (isset($menuItem['__children']) && !empty($menuItem['__children']))
      {
        $this->_buildMenuHierarchy($menuItem['__children'], $new);
      }
    }
  }
}