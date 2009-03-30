<?php

class Basesympal_registerActions extends sfActions
{
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

    $sympalContext = sfSympalContext::createInstance('sympal', $this->getContext());
    $this->renderer = $sympalContext->quickRenderContent('register');
  }
}