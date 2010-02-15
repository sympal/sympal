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
 */

abstract class sfSympalListener
{
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
  public function __construct(sfEventDispatcher $dispatcher)
  {
    if (is_array($this->getEventName()))
    {
      foreach($this->getEventName() as $name)
      {
        $dispatcher->connect($name, array($this, $this->getEntryMethod()));
      }
    }
    else
    {
      $dispatcher->connect($this->getEventName(), array($this, $this->getEntryMethod()));
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