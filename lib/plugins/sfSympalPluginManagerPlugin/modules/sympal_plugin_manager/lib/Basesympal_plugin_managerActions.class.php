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
    $this->setTemplate('index');

    $this->availablePlugins = sfSympalContext::getInstance()->getSympalConfiguration()->getOtherPlugins();
    $this->availablePlugins = array_merge($this->availablePlugins, sfSympalTools::getAvailablePlugins());
  }

  public function executeIndex()
  {
  }

  public function executeUninstall($request)
  {
    $pluginName = $request->getParameter('plugin');

    try {
      $pluginManager = new sfSympalPluginManagerUninstall();
      $pluginManager->uninstall($pluginName);

      $this->getUser()->setFlash('notice', $pluginName.' uninstalled successfully.');
    } catch (Exception $e) {
      $this->getUser()->setFlash('error', $e->getMessage());
    }
    $this->redirect('@sympal_plugin_manager');
  }

  public function executeDelete($request)
  {
    $pluginName = $request->getParameter('plugin');

    try {
      $pluginManager = new sfSympalPluginManagerUninstall();
      $pluginManager->uninstall($pluginName, null, true);

      $this->getUser()->setFlash('notice', $pluginName.' deleted successfully.');
    } catch (Exception $e) {
      $this->getUser()->setFlash('error', $e->getMessage());
    }
    $this->redirect('@sympal_plugin_manager');
  }

  public function executeInstall($request)
  {
    $pluginName = $request->getParameter('plugin');

    sfToolkit::clearGlob(sfConfig::get('sf_cache_dir'));

    try {
      $pluginManager = new sfSympalPluginManagerInstall();
      $pluginManager->install($pluginName);

      $this->getUser()->setFlash('notice', $pluginName.' installed successfully.');
    } catch (Exception $e) {
      $this->getUser()->setFlash('error', $e->getMessage());
    }
    $this->redirect('@sympal_plugin_manager');
  }

  public function executeDownload($request)
  {
    $pluginName = $request->getParameter('plugin');

    sfToolkit::clearGlob(sfConfig::get('sf_cache_dir'));

    try {
      $pluginManager = new sfSympalPluginManagerDownload();
      $pluginManager->download($pluginName);

      sfToolkit::clearGlob(sfConfig::get('sf_cache_dir'));

      $this->getUser()->setFlash('notice', $pluginName.' downloaded successfully.');
    } catch (Exception $e) {
      $this->getUser()->setFlash('error', $e->getMessage());
    }
    $this->redirect('@sympal_plugin_manager');
  }
}