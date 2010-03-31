<?php

/**
 * Class which gives you the ability to extend another class through the use of Symfony __call() events
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalExtendClass implements ArrayAccess
{
  protected $_subject;

  /**
   * Listener method for method_not_found events
   * 
   * @example
   * $extendedUser = new myExtendedUser(); // extends sfExtendClass
   * $dispatcher->connect('user.method_not_found', array($extendedUser, 'extend'));
   */
  public function extend(sfEvent $event)
  {
    $this->_subject = $event->getSubject();
    $method = $event['method'];
    $arguments = $event['arguments'];

    if (method_exists($this, $method))
    {
      $result = call_user_func_array(array($this, $method), $arguments);

      $event->setReturnValue($result);

      return true;
    }
    else
    {
      return false;
    }
  }

  /**
   * Can be used inside a magic __call method to allow for a class to be extended
   * 
   * This method will throw a class_name.method_not_found event,
   * where class_name is the "tableized" class name.
   * 
   * @example
   * public function __call($method, $arguments)
   * {
   *   return sfSympalExtendClass::extendEvent($this, $method, $arguments);
   * }
   * 
   * @param mixed $subject Instance of the class being extended
   * @param string $method The current method being called
   * @param array $arguments The arguments being passed to the above methid
   */
  public static function extendEvent($subject, $method, $arguments)
  {
    $name = sfInflector::tableize(get_class($subject));
    $event = ProjectConfiguration::getActive()->getEventDispatcher()->notifyUntil(new sfEvent($subject, $name.'.method_not_found', array('method' => $method, 'arguments' => $arguments)));
    if (!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($subject), $method));
    }

    return $event->getReturnValue();
  }

  public function offsetExists($name)
  {
    try
    {
      $this->__get($name);

      return true;
    }
    catch (sfException $e)
    {
      return false;
    }
  }

  public function getSubject()
  {
    return $this->_subject;
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

  /**
   * Allows methods to be called directly onto this object and have them
   * be passed back to the original subject
   */
  public function __call($method, $arguments)
  {
    return call_user_func_array(array($this->_subject, $method), $arguments);
  }
}