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
    $rowFormat            = "<tr class=\"%row_class%\">\n  <th>%label%</th>\n  <td>%error%%field%%help%%hidden_fields%</td>\n</tr>\n",
    $_rowClass            = 'form_row',
    $_rowErrorClass       = 'form_row_error',
    $_requiredLabelClass  = 'required';

  /**
   * Adds an extra wildcard %row_class% that is added to the row format
   * which will add an additional class if the row has an error
   * 
   * @see sfWidgetFormSchemaFormatter
   */
  public function formatRow($label, $field, $errors = array(), $help = '', $hiddenFields = null)
  {
    $row = parent::formatRow($label, $field, $errors, $help, $hiddenFields);
    
    return strtr($row, array(
      '%row_class%' => (count($errors) > 0) ? $this->_rowClass.' '.$this->_rowErrorClass : $this->_rowClass,
    ));
  }

  /**
   * Overridden to add a specific class to the label element
   * 
   * @see sfWidgetFormSchemaFormatter::generateLabel
   */
  public function generateLabel($name, $attributes = array())
  {
    $labelName = $this->generateLabelName($name);
    
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
      
      $attributes['class'] = isset($attributes['class']) ?
        $attributes['class'].' '.$this->_requiredLabelClass :
        $this->_requiredLabelClass;
      
      // if the label is &nbsp;, the dev is probably trying to hide the label
      if ($labelName != '&nbsp;')
      {
        $labelName .= ' <span class="req">*</span>';
      }
    }


    /*
     * The remainder of this function is taken right from
     * sfWidgetFormSchemaFormatter::generateLabel(), there's just no way
     * to not duplicate the code since we want to change the label's name
     */
    if (false === $labelName)
    {
      return '';
    }

    if (!isset($attributes['for']))
    {
      $attributes['for'] = $this->widgetSchema->generateId($this->widgetSchema->generateName($name));
    }

    return $this->widgetSchema->renderContentTag('label', $labelName, $attributes);
  }
}