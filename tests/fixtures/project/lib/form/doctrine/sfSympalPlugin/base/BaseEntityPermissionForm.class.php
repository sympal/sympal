<?php

/**
 * EntityPermission form base class.
 *
 * @package    form
 * @subpackage entity_permission
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseEntityPermissionForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'entity_id'     => new sfWidgetFormInputHidden(),
      'permission_id' => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'entity_id'     => new sfValidatorDoctrineChoice(array('model' => 'EntityPermission', 'column' => 'entity_id', 'required' => false)),
      'permission_id' => new sfValidatorDoctrineChoice(array('model' => 'EntityPermission', 'column' => 'permission_id', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('entity_permission[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'EntityPermission';
  }

}