<?php

class sfSympalContentSlotRenderer
{
  protected
    $_contentSlot;

  public function __construct(sfSympalContentSlot $contentSlot)
  {
    $this->_contentSlot = $contentSlot;
    $this->_content = $contentSlot->getContentRenderedFor();
  }

  public function render()
  {
    $value = $this->getRenderedValue();

    $this->translateInlineEditMessage($value);

    return $value;
  }

  public function getRawValue()
  {
    $value = $this->_contentSlot->getRawValue();

    $this->translateInlineEditMessage($value);

    return $value;
  }

  public function getRenderedValue()
  {
    if ($this->_contentSlot->render_function)
    {
      $renderFunction = $this->_contentSlot->render_function;
      if (method_exists($this->_content, $renderFunction))
      {
        return $this->_content->$renderFunction($this);
      }
      else if (method_exists($this->_contentSlot, $renderFunction))
      {
        return $this->_contentSlot->$renderFunction($this);
      }
      else
      {
        sfSympalToolkit::autoloadHelper($renderFunction);

        return $renderFunction($this->_content, $this->_contentSlot->name);
      }
    }
    else
    {
      return $this->getRawValue();
    }
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

  protected function translateInlineEditMessage(&$value)
  {
    if ('[Double click to enable inline edit mode.]' == $value)
    {
      $value = __($value);
    }
  }
}