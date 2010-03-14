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
      $this['is_column'],
      $this['name'],
      $this['content_list']
    );

    sfSympalFormToolkit::changeContentSlotTypeWidget($this);

    $this->setupValueField();
  }
  
  protected function setupValueField()
  {
    if (isset($this['value']))
    {
      $this->useFields(array('value', 'type'));
      sfSympalFormToolkit::changeContentSlotValueWidget($this->object->type, $this);
    }
  }
}