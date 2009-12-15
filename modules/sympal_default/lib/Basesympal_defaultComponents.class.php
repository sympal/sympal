<?php

class Basesympal_defaultComponents extends sfComponents
{
  public function executeLanguage(sfWebRequest $request)
  {
    $this->form = new sfFormLanguage($this->getUser(), array('languages' => sfSympalConfig::get('language_codes', null, array($this->getUser()->getCulture()))));
    unset($this->form[$this->form->getCSRFFieldName()]);
    $widgetSchema = $this->form->getWidgetSchema();
    $widgetSchema->setLabel('language', 'Select Language');

    $this->getContext()->getEventDispatcher()->notify(new sfEvent($this, 'sympal.load_change_language_form', array('form' => $this->form)));
  }
}