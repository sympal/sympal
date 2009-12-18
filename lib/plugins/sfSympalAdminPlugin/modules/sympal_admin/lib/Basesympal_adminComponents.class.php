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
  public function executeAdmin_bar()
  {
    $this->menu = new sfSympalMenuAdminBar('Sympal Admin Bar');
    $this->menu->setCredentials(array('ViewAdminBar'));

    $this->menu->addChild('Icon', null, array('label' => '<div id="sympal-icon">Sympal</div>'));
    $this->menu->addChild('Site', '@homepage');
    $this->menu->addChild('Dashboard', '@sympal_dashboard')
      ->setCredentials(array('ViewDashboard'));

    $this->menu->addChild('Administration');
    $this->menu->addChild('Security');

    $this->getContext()->getEventDispatcher()->notify(new sfEvent($this, 'sympal.load_admin_bar', array('menu' => $this->menu)));
  }
}