<?php

class sfSympalFormMethodNotFoundListener extends sfSympalListener
{
  public function getEventName()
  {
    return 'form.method_not_found';
  }

  public function run(sfEvent $event)
  {
    $sympalForm = new sfSympalForm();
    return $sympalForm->extend($event);
  }
}