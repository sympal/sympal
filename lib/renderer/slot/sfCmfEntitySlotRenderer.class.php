<?php
class sfSympalEntitySlotRenderer
{
  protected
    $_entitySlot,
    $_rawValue;

  public function __construct(EntitySlot $entitySlot)
  {
    $this->_entitySlot = $entitySlot;
  }

  public function render()
  {
    return (string) $this->getRawValue();
  }

  public function getRawValue()
  {
    if (!$this->_rawValue)
    {
      $value = $this->_entitySlot->getValue();

      if (!sfSympalConfig::get('disallow_php_in_content'))
      {
        $variables = array(
          'entity' => $this->_entitySlot->RelatedEntity,
          'menuItem' => $this->_entitySlot->RelatedEntity->MenuItem
        );
        $value = sfSympalTools::renderContent($value, $variables);
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