<?php

/**
 * ContentSlotTranslation form base class.
 *
 * @package    form
 * @subpackage content_slot_translation
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseContentSlotTranslationForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'    => new sfWidgetFormInputHidden(),
      'value' => new sfWidgetFormTextarea(),
      'lang'  => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'id'    => new sfValidatorDoctrineChoice(array('model' => 'ContentSlotTranslation', 'column' => 'id', 'required' => false)),
      'value' => new sfValidatorString(array('required' => false)),
      'lang'  => new sfValidatorDoctrineChoice(array('model' => 'ContentSlotTranslation', 'column' => 'lang', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('content_slot_translation[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'ContentSlotTranslation';
  }

}