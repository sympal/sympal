<?php
class sfWidgetFormSympalMultiLineText extends sfWidgetFormTextarea
{
  public function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);
    $this->setAttribute('style', 'width: 500px; height: 300px;');
  }
}