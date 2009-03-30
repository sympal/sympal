<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class PluginContentSlot extends BaseContentSlot
{
  protected $_saveContent = false;

  public function render()
  {
    if ($this->render_function)
    {
      $renderFunction = $this->render_function;
      if (method_exists($this->RelatedContent, $renderFunction))
      {
        return $this->RelatedContent->$renderFunction($this);
      } else {
        sfSympalToolkit::autoloadHelper($renderFunction);
        return $renderFunction($this->RelatedContent, $this->name);
      }
    } else {
      $class = 'sfSympalContentSlot'.$this->Type->name.'Renderer';

      if (!class_exists($class))
      {
        $class = 'sfSympalContentSlotRenderer';
      }

      $renderer = new $class($this);
      return (string) $renderer;
    }
  }

  public function construct()
  {
    $this->setValue($this->getValue());
  }

  public function getValue()
  {
    if ($this->is_column)
    {
      $name = $this->name;
      return $this->RelatedContent->$name;
    } else {
      return $this->getI18n('value');
    }
  }

  public function setValue($value)
  {
    if ($this->is_column)
    {
      $name = $this->name;
      return $this->RelatedContent->$name = $value;
    } else {
      return $this->setI18n('value', $value);
    }
  }
}