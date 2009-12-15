<?php

/**
 * Base actions for the sfSympalPlugin sympal_upgrade module.
 * 
 * @package     sfSympalPlugin
 * @subpackage  sympal_upgrade
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class Basesympal_upgradeActions extends sfActions
{
  public function preExecute()
  {
    parent::preExecute();

    $this->useAdminLayout();

    $this->upgrade = new sfSympalUpgradeFromWeb(
      $this->getContext()->getConfiguration(),
      $this->getContext()->getEventDispatcher(),
      new sfFormatter()
    );
    $this->hasNewVersion = $this->upgrade->hasNewVersion();
    $this->latestVersion = $this->upgrade->getLatestVersion();
    $this->currentVersion = $this->upgrade->getCurrentVersion();

    $this->checkFilePermissions();
  }

  public function redirectIfPermissionsError()
  {
    if (!$this->checkFilePermissions())
    {
      $this->redirect('@sympal_check_for_updates');
    }
  }

  public function executeUpgrade(sfWebRequest $request)
  {
    $this->redirectIfPermissionsError();

    if (!$this->hasNewVersion)
    {
      $this->setTemplate('check');
    }

    $this->askConfirmation(
      sprintf('Upgrade to Sympal %s', $this->latestVersion),
      'sympal_upgrade/confirm_upgrade',
      array('upgrade' => $this->upgrade)
    );

    $this->upgrade->download();

    $this->redirect('@sympal_run_upgrade_tasks');
  }

  public function executeRun_tasks(sfWebRequest $request)
  {
    if ($this->upgrade->hasUpgrades())
    {
      $upgrades = array();
      foreach ($this->upgrade->getUpgrades() as $upgrade)
      {
        $upgrades[] = $upgrade['version'].' upgrade #'.$upgrade['number'];
      }

      $this->askConfirmation(
        sprintf('Run upgrade tasks for Sympal %s', $this->latestVersion),
        'sympal_upgrade/confirm_run_tasks',
        array('upgrades' => $upgrades)
      );

      $this->upgrade->upgrade();
    }

    $this->getUser()->setFlash('notice', 'Successfully upgraded to '.$this->latestVersion);
    $this->redirect('@sympal_check_for_updates');
  }

  public function executeCheck(sfWebRequest $request)
  {
    $this->commands = $this->upgrade->getUpgradeCommands();
  }
}