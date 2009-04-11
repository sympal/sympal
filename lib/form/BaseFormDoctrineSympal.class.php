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
        $this->widgetSchema->setHelp('revert_to_version', 'Choose the version of this record you wish to revert to. It will take you to a screen where you can confirm.');
        $this->validatorSchema['revert_to_version'] = new sfValidatorPass(array('required' => false));
      }
    }

    sfProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($this, 'sympal.sf_form_doctrine.setup'));
  }

  public function __call($method, $arguments)
  {
    $event = sfProjectConfiguration::getActive()->getEventDispatcher()->notifyUntil(new sfEvent($this, 'sympal.sf_form_doctrine.method_not_found', array('method' => $method, 'arguments' => $arguments)));
    if (!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }

    return $event->getReturnValue();
  }
}