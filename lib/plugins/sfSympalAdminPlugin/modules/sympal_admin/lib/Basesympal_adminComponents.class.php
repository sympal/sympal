<?php

/**
 * Base components for the sfSympalAdminPlugin sympal_admin module.
 * 
 * @package     sfSympalAdminPlugin
 * @subpackage  sympal_admin
 * @author      Your name here
 * @version     SVN: $Id: BaseComponents.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class Basesympal_adminComponents extends sfComponents
{
  public function executeMenu()
  {
    $this->menu = new sfSympalMenuAdminBar('Sympal Admin');
    $this->menu->setCredentials(array('ViewAdminBar'));

    $this->menu->addChild('Go to Site', '@homepage');
    $this->menu->addChild('My Dashboard', '@sympal_dashboard');
    $this->menu->addChild('Administration', $this->getRequest()->getUri());
    $this->menu->addChild('Security', $this->getRequest()->getUri());

    $this->getContext()->getEventDispatcher()->notify(new sfEvent($this->menu, 'sympal.load_admin_menu'));
  }
}