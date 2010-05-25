<?php

/**
 * Generic menu class allows a menu item to be shown if the current user
 * has credentials matching its credentials or any of the credentials
 * of its children
 * 
 * @package     sfSympalAdminPlugin
 * @subpackage  menu
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalMenuAdminMenu extends sfSympalMenu
{
  /**
   * Overridden to be displayed if at least one of the children menus
   * should be displayed
   * 
   * @see sfSympalMenu
   */
  public function checkUserAccess(sfUser $user = null)
  {
    if (parent::checkUserAccess())
    {
      return true;
    }

    foreach ($this->getChildren() as $child)
    {
      if ($child->checkUserAccess($user))
      {
        return true;
      }
    }
    
    return false;
  }
}