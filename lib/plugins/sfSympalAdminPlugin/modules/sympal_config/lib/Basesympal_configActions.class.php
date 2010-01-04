<?php

/**
 * Base actions for the sfSympalPlugin sympal_config module.
 * 
 * @package     sfSympalPlugin
 * @subpackage  sympal_config
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class Basesympal_configActions extends sfActions
{
  public function preExecute()
  {
    parent::preExecute();

    $this->useAdminTheme();
  }

  protected function _getForm()
  {
    $class = sfSympalConfig::get('config_form_class', null, 'sfSympalConfigForm');
    $this->form = new $class();
    return $this->form;
  }

  public function executeIndex(sfWebRequest $request)
  {
    $this->form = $this->_getForm();
  }

  public function executeSave(sfWebRequest $request)
  {
    $this->form = $this->_getForm();
    $this->form->bind($request->getParameter($this->form->getName()));

    if ($this->form->isValid())
    {
      $this->dispatcher->notify(new sfEvent($this, 'sympal.pre_save_config_form', array('form' => $this->form)));

      $this->form->save();

      $this->dispatcher->notify(new sfEvent($this, 'sympal.post_save_config_form', array('form' => $this->form)));

      $this->getUser()->setFlash('notice', 'Settings updated successfully!');
      $this->redirect('@sympal_config');
    }
    $this->setTemplate('index');
  }
}