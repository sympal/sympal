<?php

/**
 * ContentSlot form base class.
 *
 * @package    form
 * @subpackage content_slot
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseContentSlotForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                   => new sfWidgetFormInputHidden(),
      'content_id'           => new sfWidgetFormDoctrineChoice(array('model' => 'Content', 'add_empty' => false)),
      'content_slot_type_id' => new sfWidgetFormDoctrineChoice(array('model' => 'ContentSlotType', 'add_empty' => false)),
      'is_column'            => new sfWidgetFormInputCheckbox(),
      'name'                 => new sfWidgetFormInput(),
    ));

    $this->setValidators(array(
      'id'                   => new sfValidatorDoctrineChoice(array('model' => 'ContentSlot', 'column' => 'id', 'required' => false)),
      'content_id'           => new sfValidatorDoctrineChoice(array('model' => 'Content')),
      'content_slot_type_id' => new sfValidatorDoctrineChoice(array('model' => 'ContentSlotType')),
      'is_column'            => new sfValidatorBoolean(array('required' => false)),
      'name'                 => new sfValidatorString(array('max_length' => 255)),
    ));

    $this->widgetSchema->setNameFormat('content_slot[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'ContentSlot';
  }

}