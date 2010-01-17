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
      $this['render_function'],
      $this['name'],
      $this['content_list']
    );

    $slotTypes = (sfSympalConfig::get('content_slot_types', null, array()));
    $choices = array();
    foreach ($slotTypes as $key => $value)
    {
      $choices[$key] = $value['label'];
    }
    $this->widgetSchema['type'] = new sfWidgetFormChoice(array('choices' => $choices));
    $this->validatorSchema['type'] = new sfValidatorChoice(array('choices' => array_keys($choices)));
    
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
}