<?php

/**
 * Base actions for the sfTestPlugin sympal_dashboard module.
 * 
 * @package     sfTestPlugin
 * @subpackage  sympal_dashboard
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class Basesympal_dashboardActions extends sfActions
{
  public function executeIndex()
  {
    if ($this->isAjax = $this->isAjax())
    {
      $this->setLayout(false);
    }

    if (sfSympalConfig::get('check_for_upgrades_on_dashboard', null, false))
    {
      $this->upgrade = new sfSympalUpgradeFromWeb(
        $this->getContext()->getConfiguration(),
        $this->getContext()->getEventDispatcher(),
        new sfFormatter()
      );

      $this->hasNewVersion = $this->upgrade->hasNewVersion();
    } else {
      $this->hasNewVersion = false;
    }
  }
}