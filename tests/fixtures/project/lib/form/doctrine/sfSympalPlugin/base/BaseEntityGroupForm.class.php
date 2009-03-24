<?php

/**
 * ContentGroup form base class.
 *
 * @package    form
 * @subpackage content_group
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseContentGroupForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'content_id' => new sfWidgetFormInputHidden(),
      'group_id'  => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'content_id' => new sfValidatorDoctrineChoice(array('model' => 'ContentGroup', 'column' => 'content_id', 'required' => false)),
      'group_id'  => new sfValidatorDoctrineChoice(array('model' => 'ContentGroup', 'column' => 'group_id', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('content_group[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'ContentGroup';
  }

}