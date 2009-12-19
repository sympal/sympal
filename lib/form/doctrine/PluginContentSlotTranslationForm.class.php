<?php

/**
 * PluginContentSlotTranslation form.
 *
 * @package    form
 * @subpackage sfSympalContentSlotTranslation
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginsfSympalContentSlotTranslationForm extends BasesfSympalContentSlotTranslationForm
{
  public function setup()
  {
    parent::setup();

    if (isset($this['value']))
    {
      sfSympalFormToolkit::changeContentSlotValueWidget($this->object->ContentSlot, $this);
    }
  }
}