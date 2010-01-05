<?php

class sfSympalDataGridPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    $this->dispatcher->connect('context.load_factories', array($this, 'listenToContextLoadFactories'));
  }

  public function listenToContextLoadFactories(sfEvent $event)
  {
    sfSympalDataGrid::setSymfonyContext($event->getSubject());
  }
}