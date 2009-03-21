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
      'user_id'       => new sfWidgetFormDoctrineChoice(array('model' => 'sfGuardUser', 'add_empty' => true)),
      'first_name'    => new sfWidgetFormInput(),
      'last_name'     => new sfWidgetFormInput(),
      'email_address' => new sfWidgetFormInput(),
      'entity_id'     => new sfWidgetFormDoctrineChoice(array('model' => 'Entity', 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'id'            => new sfValidatorDoctrineChoice(array('model' => 'UserProfile', 'column' => 'id', 'required' => false)),
      'user_id'       => new sfValidatorDoctrineChoice(array('model' => 'sfGuardUser', 'required' => false)),
      'first_name'    => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'last_name'     => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'email_address' => new sfValidatorEmail(array('max_length' => 255, 'required' => false)),
      'entity_id'     => new sfValidatorDoctrineChoice(array('model' => 'Entity', 'required' => false)),
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