<?php

class sfWidgetFormSympalWidget extends sfWidgetFormChoice
{
  public function __construct($options = array(), $attributes = array())
  {
    $widgets = sfSympalConfig::get('slot_widgets', null, array());
    $widgets = array_flip($widgets);

    array_unshift($widgets, '');
    $options['choices'] = $widgets;

    parent::__construct($options, $attributes);
  }
}