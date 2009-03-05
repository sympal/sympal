<?php

/**
 * EntitySlot form base class.
 *
 * @package    form
 * @subpackage entity_slot
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseEntitySlotForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                  => new sfWidgetFormInputHidden(),
      'entity_id'           => new sfWidgetFormDoctrineChoice(array('model' => 'Entity', 'add_empty' => false)),
      'entity_slot_type_id' => new sfWidgetFormDoctrineChoice(array('model' => 'EntitySlotType', 'add_empty' => false)),
      'name'                => new sfWidgetFormInput(),
    ));

    $this->setValidators(array(
      'id'                  => new sfValidatorDoctrineChoice(array('model' => 'EntitySlot', 'column' => 'id', 'required' => false)),
      'entity_id'           => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
      'entity_slot_type_id' => new sfValidatorDoctrineChoice(array('model' => 'EntitySlotType')),
      'name'                => new sfValidatorString(array('max_length' => 255)),
    ));

    $this->widgetSchema->setNameFormat('entity_slot[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'EntitySlot';
  }

}