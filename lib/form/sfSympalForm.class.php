<?php

class sfSympalForm extends sfSympalExtendClass
{
  public function getRequiredFields(sfValidatorSchema $validatorSchema = null, $format = null)
  {
    if ($validatorSchema === null)
    {
      $validatorSchema = $this->getValidatorSchema();
    }
    if ($format === null)
    {
      $format = $this->getWidgetSchema()->getNameFormat();
    }
    $fields = array();

    foreach ($validatorSchema->getFields() as $name => $validator)
    {
      $field = sprintf($format, $name);
      if ($validator instanceof sfValidatorSchema)
      {
        // recur
        $fields = array_merge(
          $fields,
          $this->getRequiredFields($validator, $field.'[%s]')
        );
      }
      else if ($validator->getOption('required'))
      {
        // this field is required
        $fields[] = $field;
      }
    }

    return $fields;
  }

  public function hasRecaptcha()
  {
    // No recaptcha in test environment
    if (sfConfig::get('sf_environment') === 'test')
    {
      return false;
    }
    $forms = sfSympalConfig::get('recaptcha_forms', null, array());
    $class = get_class($this->getSubject());

    return (in_array($class, $forms) || array_key_exists($class, $forms)) ? true : false;
  }
}