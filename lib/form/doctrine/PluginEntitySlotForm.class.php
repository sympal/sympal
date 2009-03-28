<?php

/**
 * PluginContentSlot form.
 *
 * @package    form
 * @subpackage ContentSlot
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginContentSlotForm extends BaseContentSlotForm
{
  public function setup()
  {
    parent::setup();

    unset($this['content_id'], $this['name']);

    $this->widgetSchema['content_slot_type_id']->setLabel('Slot Type');
    $this->widgetSchema['content_slot_type_id']->setAttribute('onChange', "change_content_slot_type('".$this->object['id']."', this.value)");

    if (isset($this['value']))
    {
      sfSympalTools::changeContentSlotValueWidget($this->object, $this);
    }

    sfSympalTools::embedI18n('MenuItem', $this);
  }
}