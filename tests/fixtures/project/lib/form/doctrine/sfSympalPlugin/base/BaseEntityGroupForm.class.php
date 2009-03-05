<?php

/**
 * EntityGroup form base class.
 *
 * @package    form
 * @subpackage entity_group
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseEntityGroupForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'entity_id' => new sfWidgetFormInputHidden(),
      'group_id'  => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'entity_id' => new sfValidatorDoctrineChoice(array('model' => 'EntityGroup', 'column' => 'entity_id', 'required' => false)),
      'group_id'  => new sfValidatorDoctrineChoice(array('model' => 'EntityGroup', 'column' => 'group_id', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('entity_group[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'EntityGroup';
  }

}