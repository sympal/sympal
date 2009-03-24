<?php

/**
 * UserProfile form base class.
 *
 * @package    form
 * @subpackage user_profile
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseUserProfileForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'            => new sfWidgetFormInputHidden(),
      'first_name'    => new sfWidgetFormInput(),
      'last_name'     => new sfWidgetFormInput(),
      'email_address' => new sfWidgetFormInput(),
      'body'          => new sfWidgetFormTextarea(),
      'content_id'     => new sfWidgetFormDoctrineChoice(array('model' => 'Content', 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'id'            => new sfValidatorDoctrineChoice(array('model' => 'UserProfile', 'column' => 'id', 'required' => false)),
      'first_name'    => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'last_name'     => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'email_address' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'body'          => new sfValidatorString(array('required' => false)),
      'content_id'     => new sfValidatorDoctrineChoice(array('model' => 'Content', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('user_profile[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'UserProfile';
  }

}