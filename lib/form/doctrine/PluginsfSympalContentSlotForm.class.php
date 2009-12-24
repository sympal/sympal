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

    unset(
      $this['content_slot_type_id'],
      $this['is_column'],
      $this['render_function'],
      $this['name'],
      $this['content_list']
    );

    if (isset($this['value']))
    {
      $this->useFields(array('value'));
      sfSympalFormToolkit::changeContentSlotValueWidget($this->object, $this);
    }
  }
}