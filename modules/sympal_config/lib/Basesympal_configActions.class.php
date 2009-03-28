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
  public function executeIndex(sfWebRequest $request)
  {
    $this->form = new sfSympalConfigForm();
  }

  public function executeSave(sfWebRequest $request)
  {
    $this->form = new sfSympalConfigForm();
    $this->form->bind($request->getParameter($this->form->getName()));
    if ($this->form->isValid())
    {
      $this->form->save();

      $this->getUser()->setFlash('notice', 'Settings updated successfully!');
      $this->redirect('@sympal_config');
    }
    $this->setTemplate('index');
  }
}