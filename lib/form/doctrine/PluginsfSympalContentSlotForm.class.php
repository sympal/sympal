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

    unset($this['content_id'], $this['name'], $this['is_column'], $this['render_function']);

    $this->widgetSchema['content_slot_type_id']->setLabel('Slot Type');
    $this->widgetSchema['content_slot_type_id']->setAttribute('onChange', "change_content_slot_type('".$this->object['id']."', this.value)");

    $q = Doctrine_Core::getTable('sfSympalContentSlotType')
      ->createQuery('t')
      ->where('t.is_internal = ?', 0)
      ->orderBy('t.name ASC');

    $this->widgetSchema['content_slot_type_id']->setOption('query', $q);

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