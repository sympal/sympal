<?php

/**
 *
 * @package symfony
 * @subpackage validator
 * @author Maxim Tsepkov <max@cluster-studio.com>
 * @author Ryan Weaver <ryan@thatsquality.com>
 */
class sfValidatorSchemaIf extends sfValidatorSchema
{

  /**
   * This validator will execute $validators only if $callable called
   * with value of $conditionalField returns true.
   *
   * @param string $conditionalField The field that must be present to force validation
   * @param string $callable Function which must return true to launch $validators
   * @param array|sfValidatorSchema $validators The validators to run
   * @param array $options
   * @param array $messages
   */
  public function __construct($conditionalField, $callable, $validators, $options = array(), $messages = array())
  {
    if (!is_string($conditionalField))
    {
      throw new sfException(sprintf('"%s" must be a string', $conditionalField));
    }
    if (!is_callable($callable))
    {
      throw new sfException(sprintf('"%s" is not callable', $callable));
    }

    $this->addOption('conditional_field', $conditionalField);
    $this->addOption('callable', $callable);
    $this->addOption('conditional_validators', $validators);

    parent::__construct(null, $options, $messages);
  }

  protected function doClean($values)
  {
    if (!call_user_func($this->getOption('callable'), $values[$this->getOption('conditional_field')]))
    {
      return $values;
    }

    // get the validators and turn them into an sfValidatorSchema for easy processing
    $validators = $this->getOption('conditional_validators');
    if (is_array($validators))
    {
      $validators = new sfValidatorSchema($validators);
    }
    elseif (!($validators instanceof sfValidatorSchema))
    {
      throw new sfException('Options conditional_validators must be an array or an instance of sfValidatorSchema.');
    }

    // have the validator schema simply ignore the extra fields
    $validators->setOption('allow_extra_fields', true);
    $validators->setOption('filter_extra_fields', false);

    return $validators->clean($values);
  }

}
