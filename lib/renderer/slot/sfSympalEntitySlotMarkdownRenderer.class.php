<?php

class sfSympalEntitySlotMarkdownRenderer extends sfSympalEntitySlotRenderer
{
  public function render()
  {
    return sfSympalMarkdownRenderer::convertToHtml($this->getRawValue());
  }
}