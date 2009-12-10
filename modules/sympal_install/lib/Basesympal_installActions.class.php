<?php

/**
 * Base actions for the sfSympalTestPlugin sympal_install module.
 * 
 * @package     sfSympalTestPlugin
 * @subpackage  sympal_install
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class Basesympal_installActions extends sfActions
{
  public function preExecute()
  {
    $this->_check();
    $this->changeTheme('install');
  }

  public function _check()
  {
    try {
      $this->checkFilePermissions();
      sfSympalToolkit::checkRequirements();
    } catch (Exception $e) {
      $this->getUser()->setFlash('error', $e->getMessage());
    }
  }

  public function executeIndex()
  {
    if (sfSympalConfig::get('installed'))
    {
      $this->redirect('@homepage');
    }

    $this->form = new sfSympalInstallForm();
  }

  public function executeRun(sfWebRequest $request)
  {
    if (sfSympalConfig::get('installed'))
    {
      $this->redirect('@homepage');
    }

    $this->setTemplate('index');

    $this->form = new sfSympalInstallForm();
    $this->form->bind($request->getParameter($this->form->getName()));
    $error = $this->getUser()->getFlash('error');
    if ($error)
    {
      $this->getUser()->setFlash('error', $error);
    }

    if ($this->form->isValid() && !$error)
    {
      $values = $this->form->getValues();

      $params = $values['user'];

      if ($values['database']['type'])
      {
        $params['db_dsn'] = $values['database']['type'].'://'.$values['database']['username'].':'.$values['database']['password'].'@'.$values['database']['host'].'/'.$values['database']['name'];
        $params['db_username'] = $values['database']['username'];
        $params['db_password'] = $values['database']['password'];
      }

      $formatter = new sfFormatter();
      try {
        chdir(sfConfig::get('sf_root_dir'));
        $install = new sfSympalInstall($this->getContext()->getConfiguration(), $this->getContext()->getEventDispatcher(), $formatter);
        $install->setParams($params);
        $install->install();
      } catch (Exception $e) {
        $this->getUser()->setFlash('error', $e->getMessage());

        return sfView::SUCCESS;
      }

      $user = Doctrine_Core::getTable('User')->findOneByUsername($values['user']['username']);
      $this->getUser()->signin($user);

      if ($values['setup']['plugins'])
      {
        $plugins = $values['setup']['plugins'];
        foreach ($plugins as $plugin)
        {
          $manager = sfSympalPluginManager::getActionInstance($plugin, 'download', $this->getContext()->getConfiguration(), $formatter);
          $manager->download();
        }
        $this->getUser()->setAttribute('sympal_install_plugins', $plugins);
        $this->redirect('@sympal_install_plugins');
      } else {
        $this->getUser()->setFlash('notice', 'Sympal installed successfully!');
        $this->redirect('@sympal_dashboard');
      }
    }
  }

  public function executeInstall_plugins(sfWebRequest $request)
  {
    $formatter = new sfFormatter();
    $plugins = $this->getUser()->getAttribute('sympal_install_plugins');

    foreach ($plugins as $plugin)
    {
      $manager = sfSympalPluginManager::getActionInstance($plugin, 'install', $this->getContext()->getConfiguration(), $formatter);
      $manager->install();
    }

    $this->getUser()->setAttribute('sympal_install_plugins', array());

    $this->getUser()->setFlash('notice', 'Sympal installed successfully!');
    $this->redirect('@sympal_dashboard');
  }
}