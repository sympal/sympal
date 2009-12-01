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

    if (sfSympalConfig::get('installed'))
    {
      $this->redirect('@homepage');
    }

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
    $this->form = new sfSympalInstallForm();
  }

  public function executeRun(sfWebRequest $request)
  {
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

      sfSympalConfig::set('sympal_install_admin_email_address', $values['email_address']);
      sfSympalConfig::set('sympal_install_admin_first_name', $values['first_name']);
      sfSympalConfig::set('sympal_install_admin_last_name', $values['last_name']);
      sfSympalConfig::set('sympal_install_admin_username', $values['username']);
      sfSympalConfig::set('sympal_install_admin_password', $values['password']);

      chdir(sfConfig::get('sf_root_dir'));
      $install = new sfSympalInstall($this->getContext()->getConfiguration(), $this->getContext()->getEventDispatcher(), new sfFormatter());
      $install->install();

      $user = Doctrine_Core::getTable('User')->findOneByUsername($values['username']);
      $this->getUser()->signin($user);

      $this->getUser()->setFlash('notice', 'Sympal installed successfully!');
      $this->redirect('@sympal_dashboard');
    }

    $this->setTemplate('index');
  }
}