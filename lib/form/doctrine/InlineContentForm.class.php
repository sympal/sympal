<?php

class InlineContentPropertyForm extends PluginContentForm
{
  public function configure()
  {
    $this->contentSlot = $this->getOption('contentSlot');
    $this->_unsetAllExcept($this->widgetSchema->getFields(), $this->contentSlot->name, $this);
    unset($this['value']);
  }

  protected function _unsetAllExcept($fields, $name, $form)
  {
    $found = false;
    $skip = null;
    foreach ($fields as $key => $value)
    {
      if ($value instanceof sfWidgetFormInputHidden)
      {
        continue;
      }

      if (!$value instanceof sfWidgetFormSchemaDecorator)
      {
        if ($key != $name)
        {
          unset($form[$key]);
        } else {
          $value->setAttribute('id', 'content_slot_value_' . $this->contentSlot['id']);
          $value->setAttribute('onKeyUp', "edit_on_key_up('".$this->contentSlot['id']."');");

          $skip = $key;
          $found = true;
        }
      } else {
        $this->_unsetAllExcept($value->getFields(), $name, $value);
      }
    }
    if ($found)
    {
      foreach ($form as $key => $value)
      {
        if ($value instanceof sfWidgetFormInputHidden)
        {
          continue;
        }

        if ($skip != $key)
        {
          unset($form[$key]);
        }
      }
    }
  }
}