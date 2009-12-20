<?php

class sfSympalContentSlotRenderer
{
  protected
    $_contentSlot,
    $_rawValue;

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
    if (!$this->_rawValue)
    {
      $value = $this->_contentSlot->getRawValue();

      if (!sfSympalConfig::get('disallow_php_in_content'))
      {
        $variables = array(
          'content' => $this->_contentSlot->getContentRenderedFor(),
          'menuItem' => $this->_contentSlot->getContentRenderedFor()->getMenuItem()
        );
        $value = sfSympalTemplate::process($value, $variables);
      }

      $event = sfApplicationConfiguration::getActive()->getEventDispatcher()->filter(new sfEvent($this, 'sympal.filter_content_slot_raw_value', array('contentSlot' => $this->_contentSlot, 'content' => $this->_contentSlot->getContentRenderedFor())), $value);
      $value = $event->getReturnValue();

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