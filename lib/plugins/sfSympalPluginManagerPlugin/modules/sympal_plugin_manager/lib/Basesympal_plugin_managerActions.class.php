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
    if (!$this->_checkFilePermissions(false))
    {
      $this->getUser()->setFlash('error', 'Some required directories are not writable.');

      $this->redirect('@sympal_plugin_manager');
    }
  }

  protected function _checkFilePermissions($flashError = true)
  {
    $dirs = array(
      sfConfig::get('sf_lib_dir').'/filter/doctrine',
      sfConfig::get('sf_lib_dir').'/form/doctrine',
      sfConfig::get('sf_lib_dir').'/model/doctrine',
      sfConfig::get('sf_root_dir').'/plugins'
    );

    $error = false;
    foreach ($dirs as $dir)
    {
      if (!is_writable($dir))
      {
        $error = true;
        if ($flashError)
        {
          $this->getUser()->setFlash('error', $dir.' is not writable.');
        }
      }
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

  public function executeBatch_action($request)
  {
    $this->_redirectIfPermissionsError();

    $plugins = $request->getParameter('plugins');
    if (empty($plugins))
    {
      $this->getUser()->setFlash('error', 'You must select at least one plugin!');
      $this->redirect('@sympal_plugin_manager');
    }

    $actions = array('install', 'uninstall', 'delete', 'download');
    foreach ($actions as $action)
    {
      if ($request->hasParameter($action))
      {
        break;
      }
    }

    foreach ($plugins as $pluginName)
    {
      $this->_executeAction($action, $pluginName);
    }

    $this->redirect('@sympal_plugin_manager');
  }

  protected function _executeAction($action, $pluginName)
  {
    try {
      sfToolkit::clearGlob(sfConfig::get('sf_cache_dir'));

      $class = 'sfSympalPluginManager'.ucfirst($action);
      $manager = new $class();
      $manager->$action($pluginName);

      sfToolkit::clearGlob(sfConfig::get('sf_cache_dir'));

      $this->getUser()->setFlash('notice', $pluginName.' "'.$action.'" action executed successfully!');

      return true;
    } catch (Exception $e) {
      $this->getUser()->setFlash('error', $pluginName.' "'.$action.'" action failed with error "'.$e->getMessage().'"!');

      return false;
    }
  }

  protected function _executeSfAction($action)
  {
    $this->_redirectIfPermissionsError();

    $request = $this->getRequest();
    $pluginName = $request->getParameter('plugin');

    $this->_executeAction($action, $pluginName);

    $this->redirect($request->getReferer());
  }
}