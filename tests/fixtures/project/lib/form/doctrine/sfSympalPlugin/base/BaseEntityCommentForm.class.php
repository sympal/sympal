<?php

/**
 * EntityComment form base class.
 *
 * @package    form
 * @subpackage entity_comment
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseEntityCommentForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'entity_id'  => new sfWidgetFormInputHidden(),
      'comment_id' => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'entity_id'  => new sfValidatorDoctrineChoice(array('model' => 'EntityComment', 'column' => 'entity_id', 'required' => false)),
      'comment_id' => new sfValidatorDoctrineChoice(array('model' => 'EntityComment', 'column' => 'comment_id', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('entity_comment[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'EntityComment';
  }

}