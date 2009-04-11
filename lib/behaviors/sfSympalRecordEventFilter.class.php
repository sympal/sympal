<?php

class sfSympalRecordEventFilter extends Doctrine_Record_Filter
{
  public function init()
  {
    $this->_eventName = sfInflector::tableize($this->_table->getOption('name'));
  }

  public function filterSet(Doctrine_Record $record, $name, $value)
  {
    $method = 'set'.sfInflector::camelize($name);
    $event = sfProjectConfiguration::getActive()->getEventDispatcher()->notifyUntil(new sfEvent($record, 'sympal.'.$this->_eventName.'.method_not_found', array('method' => $method, 'arguments' => array($value))));
    if ($event->isProcessed())
    {
      return $record;
    }

    throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
  }

  public function filterGet(Doctrine_Record $record, $name)
  {
    $method = 'get'.sfInflector::camelize($name);
    $event = sfProjectConfiguration::getActive()->getEventDispatcher()->notifyUntil(new sfEvent($record, 'sympal.'.$this->_eventName.'.method_not_found', array('method' => $method)));
    if ($event->isProcessed())
    {
      return $event->getReturnValue();
    }

    throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
  }
}