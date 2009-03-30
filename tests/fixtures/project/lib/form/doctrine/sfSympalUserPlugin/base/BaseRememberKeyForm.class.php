<?php

/**
 * RememberKey form base class.
 *
 * @package    form
 * @subpackage remember_key
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseRememberKeyForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'user_id'      => new sfWidgetFormDoctrineChoice(array('model' => 'User', 'add_empty' => true)),
      'remember_key' => new sfWidgetFormInput(),
      'ip_address'   => new sfWidgetFormInputHidden(),
      'created_at'   => new sfWidgetFormDateTime(),
      'updated_at'   => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'user_id'      => new sfValidatorDoctrineChoice(array('model' => 'User', 'required' => false)),
      'remember_key' => new sfValidatorString(array('max_length' => 32, 'required' => false)),
      'ip_address'   => new sfValidatorDoctrineChoice(array('model' => 'RememberKey', 'column' => 'ip_address', 'required' => false)),
      'created_at'   => new sfValidatorDateTime(array('required' => false)),
      'updated_at'   => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('remember_key[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'RememberKey';
  }

}