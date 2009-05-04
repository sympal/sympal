<?php

abstract class BaseFormDoctrineSympal extends sfFormDoctrine
{
  public function setup()
  {
    sfSympalFormToolkit::embedI18n($this->object, $this);

    if (sfSympalConfig::isVersioningEnabled($this->object))
    {
      unset(
        $this['version'],
        $this['previous_version']
      );

      if ($this->object->exists())
      {
        $this->widgetSchema['revert_to_version'] = new sfWidgetFormSympalVersion(array('object' => $this->object));
        $this->widgetSchema->setHelp('revert_to_version', 'Choose the version of this record you wish to revert to.');
        $this->validatorSchema['revert_to_version'] = new sfValidatorPass(array('required' => false));
      }
    }

    sfProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($this, 'sympal.sf_form_doctrine.setup'));
    sfProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($this, 'sympal.'.sfInflector::tableize(get_class($this)).'.setup'));
  }

  public function processValues($values = null)
  {
    $values = parent::processValues($values);
    $values = $this->processValuesEmbeddedForms($values);

    return $values;
  }

  public function processValuesEmbeddedForms($values = null, $forms = null)
  {
    if (is_null($forms))
    {
      $forms = $this->embeddedForms;
    }

    foreach ($forms as $name => $form)
    {
      if (!isset($values[$name]) || !is_array($values[$name]))
      {
        continue;
      }

      if ($form instanceof sfFormDoctrine)
      {
        $values[$name] = $form->processValues($values[$name]);
      }
      else
      {
        $values[$name] = $this->processValuesEmbeddedForms($values[$name], $form->getEmbeddedForms());
      }
    }

    return $values;
  }

  public function bind(array $taintedValues = null, array $taintedFiles = null)
  {
    $ret = parent::bind($taintedValues, $taintedFiles);
    foreach ($this->embeddedForms as $name => $form)
    {
      $this->embeddedForms[$name]->isBound = true;
      if (isset($this->values[$name]))
      {
        $this->embeddedForms[$name]->values = $this->values[$name];
      }
    }

    return $ret;
  }

  public function saveEmbeddedForms($con = null, $forms = null)
  {
    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    if (is_null($forms))
    {
      $forms = $this->embeddedForms;
    }

    foreach ($forms as $key => $form)
    {
      if ($form instanceof sfFormDoctrine)
      {
        $form->doSave($con);
        $form->saveEmbeddedForms($con, $form->getEmbeddedForms());
      } else {
        $this->saveEmbeddedForms($con, $form->getEmbeddedForms());
      }
    }
  }

  protected function processUploadedFile($field, $filename = null, $values = null)
  {
    if (!$this->validatorSchema[$field] instanceof sfValidatorFile)
    {
      throw new LogicException(sprintf('You cannot save the current file for field "%s" as the field is not a file.', $field));
    }

    if (is_null($values))
    {
      $values = $this->values;
    }

    if (isset($values[$field.'_delete']) && $values[$field.'_delete'])
    {
      $this->removeFile($field);

      return '';
    }

    if (!$values[$field] || !$values[$field] instanceof sfValidatedFile)
    {
      return $this->object->$field;
    }

    // we need the base directory
    if (!$this->validatorSchema[$field]->getOption('path'))
    {
      return $values[$field];
    }

    $this->removeFile($field);

    return $this->saveFile($field, $filename, $values[$field]);
  }

  public function __call($method, $arguments)
  {
    return sfSympalExtendClass::extendEvent($this, $method, $arguments);
  }
}