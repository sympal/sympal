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
      $user = $this->form->save();
      $this->getUser()->signIn($user);

      sfProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($this, 'user.register_success', array('user' => $user)));

      $this->redirect('@sympal_homepage');
    }
    $this->setTemplate('index');
  }
}