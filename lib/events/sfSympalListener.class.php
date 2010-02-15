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
    $this->_dispatcher = $dispatcher;
    $this->_invoker = $invoker;

    if (is_array($this->getEventName()))
    {
      foreach($this->getEventName() as $name)
      {
        $this->_dispatcher->connect($name, array($this, $this->getEntryMethod()));
      }
    }
    else
    {
      $this->_dispatcher->connect($this->getEventName(), array($this, $this->getEntryMethod()));
    }
  }

  /**
   * This method can be overloaded if entry point into the class should be changed.
   *
   * @return string
   */
  protected function getEntryMethod()
  {
    return 'run';
  }
}