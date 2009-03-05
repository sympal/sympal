<?php

class sympal_menuComponents extends sfComponents
{
  public function executeBreadcrumbs()
  {
    $this->breadcrumbs = $this->menuItem->getBreadcrumbs();
  }

  public function executeMenu()
  {
    $this->menu = new sfSympalMenuSite();

    $this->menuItem = Doctrine::getTable('MenuItem')->createQuery('m')
      ->select('m.*, g.*, gp.*, mp.*')
      ->leftJoin('m.Groups g')
      ->leftJoin('g.permissions gp')
      ->leftJoin('m.Permissions mp')
      ->leftJoin('m.Site s')
      ->andWhere('m.level = 0')
      ->andWhere('m.site_id = ?', 1)
      ->andWhere('m.name = ?', $this->name)
      ->fetchOne();

    $this->menu->requiresAuth($this->menuItem->requires_auth);
    $this->menu->requiresNoAuth($this->menuItem->requires_no_auth);
    $this->menu->setCredentials($this->menuItem->getAllPermissions());

    if ($this->menuItem)
    {
      $this->menuItemNode = $this->menuItem->getNode();

      $tree = Doctrine::getTable('MenuItem')->getTree();
      $q = Doctrine_Query::create()
        ->addSelect('m.*, g.*, gp.*, mp.*, e.*')
        ->from('MenuItem m INDEXBY m.id')
        ->leftJoin('m.Groups g')
        ->leftJoin('g.permissions gp')
        ->leftJoin('m.Permissions mp')
        ->leftJoin('m.RelatedEntity e')
        ->andWhere('m.level > 0');

      if (!sfSympalConfig::isEditMode())
      {
        $q->andWhere('m.is_published = 1');
      }

      $tree->setBaseQuery($q);
      $this->menuItems = $tree->fetchTree($this->menuItemNode->getRootValue());

      $hierarchy = Doctrine::getTable('MenuItem')->toHierarchy($this->menuItems->toArray());
      $this->_buildMenuHierarchy($hierarchy, $this->menu);

      $tree->resetBaseQuery();
    }
  }

  protected function _buildMenuHierarchy($hierarchy, $menu)
  {
    $user = sfContext::getInstance()->getUser();

    foreach ($hierarchy as $node)
    {
      $menuItem = $this->menuItems[$node['id']];
      $new = $menu->addNode($menuItem->getLabel(), $menuItem->getItemRoute());
      $new->requiresAuth($menuItem->requires_auth);
      $new->requiresNoAuth($menuItem->requires_no_auth);
      $new->setCredentials($menuItem->getAllPermissions());

      if (isset($node['__children']) && !empty($node['__children']))
      {
        $this->_buildMenuHierarchy($node['__children'], $new);
      }
    }
  }
}