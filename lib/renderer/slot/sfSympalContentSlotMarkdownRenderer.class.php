<?php

class sfSympalContentSlotMarkdownRenderer extends sfSympalContentSlotRenderer
{
  public function render()
  {
    return sfSympalMarkdownRenderer::convertToHtml($this->getRawValue());
  }
}