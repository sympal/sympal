<?php

/**
 * PluginEntitySlotTranslation form.
 *
 * @package    form
 * @subpackage EntitySlotTranslation
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginEntitySlotTranslationForm extends BaseEntitySlotTranslationForm
{
  public function setup()
  {
    parent::setup();

    sfSympalTools::changeEntitySlotValueWidget($this->object->EntitySlot, $this);
  }
}