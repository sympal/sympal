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
      sfSympalFormToolkit::changeContentSlotValueWidget($this->object, $this);
    }
  }
  
  public function doSave($con = null)
  {
    parent::doSave($con);
    
    /*
     * If this is a column slot, then the value was actually set on the
     * content record, meaning that we need to save that record
     */
    if ($this->getObject()->is_column)
    {
      $this->getObject()->getContentRenderedFor()->save($con);
    }
  }
}