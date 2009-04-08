<?php

class sfSympalRecord extends Doctrine_Template
{
  protected $_eventName;

  public function setInvoker(Doctrine_Record_Abstract $invoker)
  {
    parent::setInvoker($invoker);
    $this->_eventName = sfInflector::tableize(get_class($this->getInvoker()));
  }

  public function setTableDefinition()
  {
    sfProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($this->getInvoker(), 'sympal.'.$this->_eventName.'.set_table_definition', array('object' => $this)));

    $this->_table->unshiftFilter(new sfSympalRecordEventFilter());
  }

  public function setUp()
  {
    sfProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($this->getInvoker(), 'sympal.'.$this->_eventName.'.setup', array('object' => $this)));
  }

  public function __call($method, $arguments)
  {
    $event = sfProjectConfiguration::getActive()->getEventDispatcher()->notifyUntil(new sfEvent($this->getInvoker(), 'sympal.'.$this->_eventName.'.method_not_found', array('method' => $method, 'arguments' => $arguments)));
    if (!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }

    return $event->getReturnValue();
  }
}