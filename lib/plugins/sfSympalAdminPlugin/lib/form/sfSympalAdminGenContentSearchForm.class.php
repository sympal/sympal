<?php

class sfSympalAdminGenContentSearchForm extends sfSympalAdminGenSearchForm
{
  public function configure()
  {
    $this->widgetSchema['is_published'] = new sfWidgetFormChoice(array('choices' => array('' => 'All', 1 => 'Yes', 0 => 'No')));
    $this->validatorSchema['is_published'] = new sfValidatorChoice(array('required' => false, 'choices' => array('', 1, 0)));
  }

  protected function addIsPublishedColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if ($values)
    {
      $query->addWhere('r.date_published < NOW()');
    } else {
      $query->addWhere('r.date_published >= NOW() OR r.date_published IS NULL');
    }
  }
}