<?php

/**
 * ContentPermission form base class.
 *
 * @package    form
 * @subpackage content_permission
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseContentPermissionForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'content_id'    => new sfWidgetFormInputHidden(),
      'permission_id' => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'content_id'    => new sfValidatorDoctrineChoice(array('model' => 'ContentPermission', 'column' => 'content_id', 'required' => false)),
      'permission_id' => new sfValidatorDoctrineChoice(array('model' => 'ContentPermission', 'column' => 'permission_id', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('content_permission[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'ContentPermission';
  }

}