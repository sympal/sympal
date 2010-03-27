<?php

/**
 * Sympal extension of the table form formatter to add a "required" class
 * to the label of required fields
 * 
 * @package     sfSympalPlugin
 * @subpackage  widget
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @since       2010-03-27
 * @version     svn:$Id$ $Author$
 */
class sfSympalWidgetFormSchemaFormatterTable extends sfWidgetFormSchemaFormatterTable
{
  protected
    $_requiredLabelClass = 'required';
 
  public function generateLabel($name, $attributes = array())
  {
    // loop up to find the "required_fields" option
    $widget = $this->widgetSchema;
    do
    {
      $requiredFields = (array) $widget->getOption('required_fields');
    }
    while ($widget = $widget->getParent());

    // add a class (non-destructively) if the field is required
    if (in_array($this->widgetSchema->generateName($name), $requiredFields))
    {
      $attributes['class'] = isset($attributes['class']) ? $attributes['class'].' '.$this->_requiredLabelClass : $this->_requiredLabelClass;
    }

    return parent::generateLabel($name, $attributes);
  }
}