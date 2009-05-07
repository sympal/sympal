<?php

class sfWidgetFormSympalArray extends sfWidgetFormSympalMultiLineText
{
  public function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);
    $this->setAttribute('style', 'width: 800px; height: 300px;');
  }

  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $value = sfYaml::dump($value, 5);
    return parent::render($name, $value, $attributes, $errors);
  }
}