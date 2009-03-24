<?php

/**
 * sympal_register actions.
 *
 * @package    sympal
 * @subpackage sympal_register
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z jwage $
 */
class sympal_registerActions extends sfActions
{
  public function executeSave(sfWebRequest $request)
  {
    $this->form = sfSympalRegisterForm::getInstance();
    $this->form->bind($request->getParameter($this->form->getName()));
    if ($this->form->isValid())
    {
      $this->form->save();

      $this->getUser()->signIn($this->form->getObject());
      $this->redirect('@sympal_homepage');
    }

    $sympalContext = sfSympalContext::createInstance('sympal', $this->getContext());
    $this->renderer = $sympalContext->quickRenderContent('register');
  }
}