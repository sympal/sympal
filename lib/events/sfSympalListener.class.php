<?php

/**
 * A listener is an object that will react on an event which fired
 * by the dispatcher at predefined conditions.
 *
 * Whenever an instance of sfSympalListener is created it will be registered
 * in the dispatcher for an event, returned by getEventName() method.
 *
 * In trivial case to register a listener you need create a class that extends
 * sfSympalListener, define methods getEventName() and run() then create an instance
 * to register it.
 *
 * @package     sfSympalPlugin
 * @subpackage  listener
 * @author Maxim Tsepkov <azrael.com@gmail.com>
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */

abstract class sfSympalListener
{
  protected
    $_dispatcher,
    $_invoker;

  /**
   * Must return event name to connect to.
   * It can be a string for one event or an array of strings for multiple events.
   *
   * @return string|array
   */
  abstract public function getEventName();

  /**
   *
   * @param sfSympalListener $listener
   * @return null
   */
  public function __construct(sfEventDispatcher $dispatcher, $invoker)
  {
    $this->_invoker = $invoker;
    $this->_dispatcher = $dispatcher;

    $eventName = $this->getEventName();
    $entryMethod = $this->getEntryMethod();
    if (is_array($eventName))
    {
      foreach($eventName as $name)
      {
        $this->_dispatcher->connect($name, array($this, $entryMethod));
      }
    }
    else
    {
      $this->_dispatcher->connect($eventName, array($this, $entryMethod));
    }
  }

  /**
   * This method can be overloaded if entry point into the class should be changed.
   * Note that call_user_func() that Symfony dispatcher uses call this method as static.
   *
   * @return string
   */
  protected function getEntryMethod()
  {
    return 'run';
  }
}