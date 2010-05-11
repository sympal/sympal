<?php

/**
 * The central admin screen/area (dashboard).
 * 
 * @package     sfSympalAdminPlugin
 * @subpackage  actions
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
abstract class Basesympal_dashboardActions extends sfActions
{
  public function executeIndex()
  {
    $response = $this->getResponse();
    $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalPlugin/js/shortcuts.js'));
    $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalAdminPlugin/js/shortcuts.js'));

    if (sfSympalConfig::get('check_for_upgrades_on_dashboard', null, false))
    {
      $this->upgrade = new sfSympalUpgradeFromWeb(
        $this->getContext()->getConfiguration(),
        $this->getContext()->getEventDispatcher(),
        new sfFormatter()
      );

      $this->hasNewVersion = $this->upgrade->hasNewVersion();
    }
    else
    {
      $this->hasNewVersion = false;
    }
  }
}