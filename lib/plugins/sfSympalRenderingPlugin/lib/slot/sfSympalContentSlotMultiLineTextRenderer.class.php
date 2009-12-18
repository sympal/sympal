<?php

class sfSympalContentSlotMultiLineTextRenderer extends sfSympalContentSlotRenderer
{
  public function render()
  {
    return (string) nl2br($this->getRawValue());
  }
}