<?php

class sfSympalInstallForm extends sfForm
{
  public function setup()
  {
    $this->setWidgets(array(
      'first_name'      => new sfWidgetFormInput(),
      'last_name'       => new sfWidgetFormInput(),
      'email_address'   => new sfWidgetFormInput(),
      'username'        => new sfWidgetFormInput(),
      'password'        => new sfWidgetFormInputPassword(),
    ));

    $this->setValidators(array(
      'first_name'      => new sfValidatorString(array('max_length' => 255)),
      'last_name'       => new sfValidatorString(array('max_length' => 255)),
      'email_address'   => new sfValidatorString(array('max_length' => 255)),
      'username'        => new sfValidatorString(array('max_length' => 255)),
      'password'        => new sfValidatorString(),
    ));

    $this->widgetSchema['password'] = new sfWidgetFormInputPassword();
    $this->validatorSchema['password']->setOption('required', false);
    $this->widgetSchema['password_again'] = new sfWidgetFormInputPassword();
    $this->validatorSchema['password_again'] = clone $this->validatorSchema['password'];

    $this->widgetSchema->moveField('password_again', 'after', 'password');

    $this->widgetSchema->setNameFormat('install[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->mergePostValidator(new sfValidatorSchemaCompare('password', sfValidatorSchemaCompare::EQUAL, 'password_again', array(), array('invalid' => 'The two passwords must be the same.')));

    parent::setup();
  }
}