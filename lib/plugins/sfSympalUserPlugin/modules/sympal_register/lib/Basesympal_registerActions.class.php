<?php

class Basesympal_registerActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $this->form = sfSympalRegisterForm::getInstance();
  }

  public function executeSave(sfWebRequest $request)
  {
    $this->form = sfSympalRegisterForm::getInstance();

    sfSympalFormToolkit::bindFormRecaptcha($this->form, sfSympalConfig::get('sfSympalRegisterPlugin', 'enable_recaptcha'));

    if ($this->form->isValid())
    {
      $this->form->save();

      $this->getUser()->signIn($this->form->getObject());
      $this->redirect('@sympal_homepage');
    }
    $this->setTemplate('index');
  }
}