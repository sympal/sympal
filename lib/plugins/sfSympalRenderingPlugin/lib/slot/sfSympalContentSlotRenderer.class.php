<?php

class sfSympalContentSlotRenderer
{
  protected
    $_contentSlot;

  public function __construct(sfSympalContentSlot $contentSlot)
  {
    $this->_contentSlot = $contentSlot;
  }

  public function render()
  {
    return $this->getRawValue();
  }

  public function getRawValue()
  {
    return $this->_contentSlot->getRawValue();
  }

  public function __toString()
  {
    try
    {
      return (string) $this->render();
    }
    catch (Exception $e)
    {
      return sfSympalToolkit::renderException($e);
    }
  }
}