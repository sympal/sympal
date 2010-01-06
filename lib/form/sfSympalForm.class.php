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

  public static function listenToFormPostConfigure(sfEvent $event)
  {
    $form = $event->getSubject();
    if ($form instanceof sfFormDoctrine)
    {
      sfSympalFormToolkit::embedI18n($form->getObject(), $form);

      if (sfSympalConfig::get('remove_timestampable_from_forms', null, true))
      {
        unset($form['created_at'], $form['updated_at']);
      }
    }
    $widgetSchema = $form->getWidgetSchema();
    $requiredFields = $form->getRequiredFields();
    $widgetSchema->addOption('required_fields', $requiredFields);
    $widgetSchema->addFormFormatter('table', new sfSympalWidgetFormSchemaFormatterTable($widgetSchema));
  }
}