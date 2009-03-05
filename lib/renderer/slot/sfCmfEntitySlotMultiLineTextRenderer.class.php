<?php

class sfSympalEntitySlotMultiLineTextRenderer extends sfSympalEntitySlotRenderer
{
  public function render()
  {
    return (string) nl2br($this->getRawValue());
  }
}