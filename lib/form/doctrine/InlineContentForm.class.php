<?php

class InlineContentPropertyForm extends PluginContentForm
{
  
  public function setup()
  {
    $this->contentSlot = $this->getOption('contentSlot');
    parent::setup(true);

    unset($this['value']);

/*
  $value->setAttribute('id', 'content_slot_value_' . $this->contentSlot['id']);
  $value->setAttribute('onKeyUp', "edit_on_key_up('".$this->contentSlot['id']."');");
  */
  }
}