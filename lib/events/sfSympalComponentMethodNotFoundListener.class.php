<?php

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