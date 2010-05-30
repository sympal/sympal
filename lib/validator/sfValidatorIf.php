<?php

/**
 * This is a wrapper.
 *
 * Validator takes the following parameters:
 *  - form      [required] current form
 *  - fieldname [required] field name to check
 *  - callable  [optional] default is "strlen"
 *  - validator [optional] default is sfValidatorPass
 *
 * This validator will run $validator on $fieldname tainted value if and only if
 * $callable returns true for $fieldname value,
 * i.e. if $callable($field->getValue()) returns false, then validator will pass,
 * otherwise $validator will run.
 *
 * For example, if we want to set some _field_ required only if _checkbox_ is cheked,
 * we would write something like this:
 *
 *   $this->setValidator('_field_', new sfValidatorIf(array(
 *      'form' => $this,
 *      'fieldname' => '_checkbox_',
 *      'validator' => new sfValidatorDate('required' => false)
 *   )));
 *
 * @author Maxim Tsepkov <max@cluster-studio.com>
 *
 */
class sfValidatorIf extends sfValidatorBase
{

  /**
   * Overloading clean() to change logic of processing clean values.
   *
   * Normally if user would submit empty value but child validator needs
   * it (required) then validator will pass always because it would pass
   * at our level, because this validator never requires value.
   *
   */
  public function clean($value)
  {
    $clean = $value;

    if ($this->options['trim'] && is_string($clean))
    {
      $clean = trim($clean);
    }

    return $this->doClean($clean);
  }

  protected function configure($options = array(), $messages = array())
  {
    $this->addRequiredOption('form');
    $this->addRequiredOption('fieldname');
    $this->addOption('callable', 'strlen');
    $this->addOption('validator', new sfValidatorPass());
  }

  protected function doClean($value)
  {
    // checking input
    if (!$this->getOption('form') instanceof sfForm)
    {
      throw new RuntimeException(sprintf('"%s" must be an instance of sfForm', $this->getOption('form')));
    }
    if (!is_string($this->getOption('fieldname')))
    {
      throw new RuntimeException(sprintf('"%s" must be a string', $this->getOption('fieldname')));
    }
    if (!is_callable($this->getOption('callable')))
    {
      throw new RuntimeException(sprintf('"%s" is not callable', $this->getOption('callable')));
    }
    if (!$this->getOption('validator') instanceof sfValidatorBase)
    {
      throw new RuntimeException(sprintf('"%s" must be an instance of sfValidatorBase', $this->getOption('validator')));
    }

    // nominal logic
    $taintedValues = $this->getOption('form')->getTaintedValues();
    if (call_user_func($this->getOption('callable'), $taintedValues[$this->getOption('fieldname')]))
    {
      return $this->getOption('validator')->clean($value);
    }
    else
    {
      return $value;
    }
  }

}
