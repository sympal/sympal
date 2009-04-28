<?php

class sfSympalContentSlotWidgetRenderer extends sfSympalContentSlotRenderer
{
  public function render()
  {
    $e = explode('/', $this->getRawValue());

    if (!isset($e[0]) || !isset($e[1]))
    {
      return null;
    }

    return sfSympalToolkit::getSymfonyResource($e[0], $e[1], array('content' => $this->_contentSlot->RelatedContent, 'contentSlot' => $this->_contentSlot));
  }
}