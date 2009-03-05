<?php

/**
 * sympal_config actions.
 *
 * @package    sympal
 * @subpackage sympal_config
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z jwage $
 */
class sympal_configActions extends sfActions
{
  public function preExecute()
  {
    parent::preExecute();
    sfSympalTools::changeLayout(sfSympalConfig::get('default_layout'));
  }

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