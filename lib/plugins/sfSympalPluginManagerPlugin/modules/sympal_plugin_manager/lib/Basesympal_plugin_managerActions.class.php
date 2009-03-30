<?php

/**
 * Base actions for the sfSympalPluginManagerPlugin sympal_plugin_manager module.
 * 
 * @package     sfSympalPluginManagerPlugin
 * @subpackage  sympal_plugin_manager
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class Basesympal_plugin_managerActions extends sfActions
{
  public function preExecute()
  {
    $sympalConfiguration = sfSympalContext::getInstance()->getSympalConfiguration();

    $this->addonPlugins = $sympalConfiguration->getAllManageablePlugins();
    $this->corePlugins = $sympalConfiguration->getCorePlugins();
    $this->installedPlugins = $sympalConfiguration->getInstalledPlugins();

    $this->_checkFilePermissions();
  }

  protected function _redirectIfPermissionsError()
  {
    if (!$this->_checkFilePermissions())
    {
      $this->redirect('@sympal_plugin_manager');
    }
  }

  protected function _checkFilePermissions()
  {
    $checks = sfFinder::type('file')->in(sfConfig::get('sf_root_dir'));
    $checks = array_merge($checks, sfFinder::type('dir')->in(sfConfig::get('sf_root_dir')));

    $error = false;
    foreach ($checks as $check)
    {
      if (!is_writable($check))
      {
        $error = true;
      }
    }
    if ($error)
    {
      $this->getUser()->setFlash('error', 'You have some permissions problems. The plugin manager requires your symfony application directories to be writable by your web server.');
    }

    if ($error)
    {
      return false;
    } else {
      return true;
    }
  }

  public function executeIndex()
  {
  }

  public function executeView($request)
  {
    $key = array_search($request->getParameter('plugin'), $this->installedPlugins);
    $this->plugin = new sfSympalPluginInfo($this->addonPlugins[$key]);
  }

  public function executeUninstall($request)
  {
    $this->_executeSfAction('uninstall');
  }

  public function executeDelete($request)
  {
    $this->_executeSfAction('delete');
  }

  public function executeInstall($request)
  {
    $this->_executeSfAction('install');
  }

  public function executeDownload($request)
  {
    $this->_executeSfAction('download');
  }

  protected function _executeAction($action, $pluginName)
  {
    try {
      sfToolkit::clearGlob(sfConfig::get('sf_cache_dir'));

      $manager = sfSympalPluginManager::getActionInstance($pluginName, $action);
      $manager->$action();

      sfToolkit::clearGlob(sfConfig::get('sf_cache_dir'));

      $this->getUser()->setFlash('notice', $pluginName.' "'.$action.'" action executed successfully!');

      return true;
    } catch (Exception $e) {
      $this->getUser()->setFlash('error', $pluginName.' "'.$action.'" action failed with error "'.$e->getMessage().'"!'.(sfConfig::get('sf_debug') ? "<br/><br/>".nl2br($e->getTraceAsString()):null));

      return false;
    }
  }

  protected function _executeSfAction($action)
  {
    $this->_redirectIfPermissionsError();

    $request = $this->getRequest();
    $pluginName = $request->getParameter('plugin');

    $title = ucfirst($action).' '.$pluginName;
    $message = 'Are you sure you wish to run the action "'.$action.'" on the plugin named '.$pluginName.'?';
    $this->askConfirmation($title, $message);

    $this->_executeAction($action, $pluginName);

    $this->redirect($request->getParameter('redirect_url'));
  }
}