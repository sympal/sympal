<?php

class Basesympal_defaultActions extends sfActions
{
  public function preExecute()
  {
    sfSympalTheme::loadDefault();
  }

  public function executeNew_site(sfWebRequest $request)
  {
    
  }

  public function executeChange_language(sfWebRequest $request)
  {
    $oldCulture = $this->getUser()->getCulture();
    $this->form = new sfFormLanguage($this->getUser(), array('languages' => sfSympalConfig::get('language_codes', null, array($this->getUser()->getCulture()))));
    unset($this->form[$this->form->getCSRFFieldName()]);

    $this->form->process($request);

    $newCulture = $this->getUser()->getCulture();

    $this->getUser()->setFlash('notice', 'Changed language successfully!');

    return $this->redirect(str_replace('/'.$oldCulture.'/', '/'.$newCulture.'/', $this->getUser()->getReferer('@homepage')));
  }

  public function executeAsk_confirmation(sfWebRequest $request)
  {
    $this->useAdminLayout();

    $this->url = $request->getUri();
    $this->title = $request->getAttribute('title');
    $this->message = $request->getAttribute('message');
  }

  public function executeSecure()
  {
  }

  public function executeError404()
  {
  }

  public function executeDisabled()
  {
    
  }
}