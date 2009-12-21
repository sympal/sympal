<?php

/**
 * PluginContentSlot form.
 *
 * @package    form
 * @subpackage sfSympalContentSlot
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginsfSympalContentSlotForm extends BasesfSympalContentSlotForm
{
  public function setup()
  {
    parent::setup();

    $this->useFields(array('value'));

    if (isset($this['value']))
    {
      sfSympalFormToolkit::changeContentSlotValueWidget($this->object, $this);
    }
  }

  protected $_widgets = array();

  protected function _findWidgets($form)
  {
    foreach ($form as $key => $value)
    {
      if ($value instanceof sfFormFieldSchema)
      {
        $label = strip_tags($value->renderLabel());

        foreach ($value as $k => $v)
        {
          if ($v instanceof sfFormFieldSchema)
          {
            $label = strip_tags($v->renderLabel());

            $this->_findWidgets($v);
          } else {
            if ($v->getName() == $this->object->name)
            {
              $this->_widgets[] = $v;
            }
          }
        }
      } else {
        if ($value->getName() == $this->object->name)
        {
          $this->_widgets[] = $value;
        }
      }
    }
    return $this->_widgets;
  }

  public function renderSlotForm()
  {
    $widgets = $this->_findWidgets($this);

    $return = '';

    if ($this->hasGlobalErrors())
    {
      $return .= $this->renderGlobalErrors();
    }

    $return .= $this->renderHiddenFields();
    foreach ($this->_widgets as $widget)
    {
      if ($widget->isHidden())
      {
        $return .= $widget;
      } else {
        $return .= '<div class="sf_admin_form_row">';
        $return .= $widget->renderLabel();
        $return .= $widget;
        $return .= $widget->renderHelp();
        $return .= '</div>';
      }
    }

    return $return;
  }

  public function __toString()
  {
    return '<table>'.parent::__toString().'</table>';
  }
}