<?php

class sfSympalRenderingPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    $this->dispatcher->connect('sympal.filter_content_slot_raw_value', array($this, 'filterSympalContentSlotRawValue'));
  }

  public function filterSympalContentSlotRawValue(sfEvent $event, $content)
  {
    $content = preg_replace_callback("/##(.*)\/(.*)##/", array($this, '_replaceSymfonyResources'), $content);
    return $content;
  }

  private function _replaceSymfonyResources($matches)
  {
    try {
      list($match, $module, $action) = $matches;
      return sfSympalToolkit::getSymfonyResource($module, $action);
    } catch (Exception $e) {
      return sfSympalToolkit::renderException($e);
    }
  }
}