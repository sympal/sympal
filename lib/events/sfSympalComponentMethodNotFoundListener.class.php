<?php

/**
 * Effectively extends sfComponents to use sfSympalActions
 * 
 * @package     sfSympalPlugin
 * @subpackage  events
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @since       2010-03-27
 * @version     svn:$Id$ $Author$
 */
class sfSympalComponentMethodNotFoundListener extends sfSympalListener
{
  public function getEventName()
  {
    return 'component.method_not_found';
  }

  public function run(sfEvent $event)
  {
    $actions = new sfSympalActions();
    return $actions->extend($event);
  }
}