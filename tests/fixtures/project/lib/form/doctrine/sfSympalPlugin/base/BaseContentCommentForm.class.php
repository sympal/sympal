<?php

/**
 * ContentComment form base class.
 *
 * @package    form
 * @subpackage content_comment
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseContentCommentForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'content_id' => new sfWidgetFormInputHidden(),
      'comment_id' => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'content_id' => new sfValidatorDoctrineChoice(array('model' => 'ContentComment', 'column' => 'content_id', 'required' => false)),
      'comment_id' => new sfValidatorDoctrineChoice(array('model' => 'ContentComment', 'column' => 'comment_id', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('content_comment[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'ContentComment';
  }

}