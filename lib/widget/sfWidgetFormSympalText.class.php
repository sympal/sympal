<?php
class sfWidgetFormSympalText extends sfWidgetFormInput
{
  public function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);
    $this->setAttribute('style', 'width: 400px;');
  }
}