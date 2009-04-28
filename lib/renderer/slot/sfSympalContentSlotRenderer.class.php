<?php

class sfSympalContentSlotRenderer
{
  protected
    $_contentSlot,
    $_rawValue;

  public function __construct(ContentSlot $contentSlot)
  {
    $this->_contentSlot = $contentSlot;
  }

  public function render()
  {
    return $this->getRawValue();
  }

  public function getRawValue()
  {
    if (!$this->_rawValue)
    {
      if (sfContext::hasInstance() && sfContext::getInstance()->getUser()->hasUnsavedContentSlotValue($this->_contentSlot))
      {
        $value = sfContext::getInstance()->getUser()->getUnsavedContentSlotValue($this->_contentSlot);
      } else {
        $value = $this->_contentSlot->getRawValue();
      }

      if (!sfSympalConfig::get('disallow_php_in_content'))
      {
        $variables = array(
          'content' => $this->_contentSlot->RelatedContent,
          'menuItem' => $this->_contentSlot->RelatedContent->MenuItem
        );
        $value = sfSympalToolkit::processPhpCode($value, $variables);
      }
  
      $this->_rawValue = $value;
    }
    return $this->_rawValue;
  }

  public function __toString()
  {
    try {
      return (string) $this->render();
    } catch (Exception $e) {
      return $e->getMessage();
    }
  }

  public function __call($method, $arguments)
  {
    return sfSympalExtendClass::extendEvent($this, $method, $arguments);
  }
}