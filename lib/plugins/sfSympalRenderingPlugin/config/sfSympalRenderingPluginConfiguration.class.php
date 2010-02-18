<?php

class sfSympalRenderingPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    $this->dispatcher->connect('sympal.load', array($this, 'listenToSympalLoad'));
  }

  public function listenToSympalLoad(sfEvent $event)
  {
    new sfSympalRenderingResponseFilterContent($this->dispatcher, $this);
  }
}