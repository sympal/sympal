<?php

class Basesympal_defaultComponents extends sfComponents
{
  public function executeLanguage(sfWebRequest $request)
  {
    $this->form = new sfFormLanguage($this->getUser(), array('languages' => sfSympalConfig::get('language_codes', null, array($this->getUser()->getCulture()))));
    unset($this->form[$this->form->getCSRFFieldName()]);
    $widgetSchema = $this->form->getWidgetSchema();
    $widgetSchema->setLabel('language', 'Select Language');
  }
}