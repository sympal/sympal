<?php

/**
 * PluginEntitySlot form.
 *
 * @package    form
 * @subpackage EntitySlot
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginEntitySlotForm extends BaseEntitySlotForm
{
  public function setup()
  {
    parent::setup();

    unset($this['entity_id'], $this['name']);

    $this->widgetSchema['entity_slot_type_id']->setLabel('Slot Type');
    $this->widgetSchema['entity_slot_type_id']->setAttribute('onChange', "change_entity_slot_type('".$this->object['id']."', this.value)");

    if (isset($this['value']))
    {
      sfSympalTools::changeEntitySlotValueWidget($this->object, $this);
    }

    sfSympalTools::embedI18n('menus', $this);
  }
}