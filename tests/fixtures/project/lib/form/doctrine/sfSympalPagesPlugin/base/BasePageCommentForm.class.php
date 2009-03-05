<?php

/**
 * PageComment form base class.
 *
 * @package    form
 * @subpackage page_comment
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BasePageCommentForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'page_id'    => new sfWidgetFormInputHidden(),
      'comment_id' => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'page_id'    => new sfValidatorDoctrineChoice(array('model' => 'PageComment', 'column' => 'page_id', 'required' => false)),
      'comment_id' => new sfValidatorDoctrineChoice(array('model' => 'PageComment', 'column' => 'comment_id', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('page_comment[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'PageComment';
  }

}