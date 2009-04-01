<?php
class sfSympalMenuSite extends sfSympalMenu
{
  protected 
    $_menuItem = null;

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

    $currentMenuItem = sfSympalToolkit::getCurrentMenuItem();

    if ($currentMenuItem && $currentMenuItem->exists())
    {
      $this->isCurrent($menuItem->id == $currentMenuItem->id);
    }

    $this->setLevel($menuItem->level);
  }

  public function getMenuItemSubMenu($menuItem)
  {
    foreach ($this->_children as $child)
    {
      if ($child->getMenuItem()->id == $menuItem->id && $child->getChildren())
      {
        $result = $child;
      } else if ($n = $child->getMenuItemSubMenu($menuItem)) {
        $result = $n;
      }

      if (isset($result))
      {
        $class = get_class($result->getParent());
        $instance = new $class($child->getName());
        $instance->setMenuItem($menuItem);
        $instance->setChildren($result->getChildren());

        return $instance;
      }
    }
  }
}