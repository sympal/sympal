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
    return (string) $this->getRawValue();
  }

  public function getRawValue()
  {
    if (!$this->_rawValue)
    {
      $value = $this->_contentSlot->getValue();

      if (!sfSympalConfig::get('disallow_php_in_content'))
      {
        $variables = array(
          'content' => $this->_contentSlot->RelatedContent,
          'menuItem' => $this->_contentSlot->RelatedContent->MenuItem
        );
        $value = sfSympalTools::processPhpCode($value, $variables);
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
}