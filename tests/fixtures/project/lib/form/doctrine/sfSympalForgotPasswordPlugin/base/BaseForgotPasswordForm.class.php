<?php

/**
 * ForgotPassword form base class.
 *
 * @package    form
 * @subpackage forgot_password
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseForgotPasswordForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'user_id'    => new sfWidgetFormDoctrineChoice(array('model' => 'sfGuardUser', 'add_empty' => false)),
      'unique_key' => new sfWidgetFormInput(),
      'expires_at' => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorDoctrineChoice(array('model' => 'ForgotPassword', 'column' => 'id', 'required' => false)),
      'user_id'    => new sfValidatorDoctrineChoice(array('model' => 'sfGuardUser')),
      'unique_key' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'expires_at' => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('forgot_password[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'ForgotPassword';
  }

}