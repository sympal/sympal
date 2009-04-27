<?php

class sfSympalExtendClass implements ArrayAccess
{
  protected $_subject;

  public function offsetExists($name)
  {
    try {
      $this->__get($name);
      return true;
    } catch (sfException $e) {
      return false;
    }
  }

  public function offsetGet($name)
  {
    return $this->__get($name);
  }

  public function offsetSet($name, $value)
  {
    return $this->__set($name, $value);
  }

  public function offsetUnset($name)
  {
    unset($this->_subject->$name);
  }

  public function __get($name)
  {
    return $this->_subject->$name;
  }

  public function __set($name, $value)
  {
    $this->_subject->$name = $value;
  }

  public static function extendEvent($subject, $method, $arguments)
  {
    $name = sfInflector::tableize(get_class($subject));
    $event = ProjectConfiguration::getActive()->getEventDispatcher()->notifyUntil(new sfEvent($subject, 'sympal.'.$name.'.method_not_found', array('method' => $method, 'arguments' => $arguments)));
    if (!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($subject), $method));
    }

    return $event->getReturnValue();
  }

  public function extend(sfEvent $event)
  {
    $this->_subject = $event->getSubject();
    $method = $event['method'];
    $arguments = $event['arguments'];

    try {
      $result = call_user_func_array(array($this, $method), $arguments);

      $event->setProcessed(true);
      $event->setReturnValue($result);

      return $result;
    } catch (Exception $e) {}
  }

  public function __call($method, $arguments)
  {
    return call_user_func_array(array($this->_subject, $method), $arguments);
  }
}