<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/doctrine/BaseFormFilterDoctrine.class.php');

/**
 * UserProfile filter form base class.
 *
 * @package    filters
 * @subpackage UserProfile *
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class BaseUserProfileFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'first_name'    => new sfWidgetFormFilterInput(),
      'last_name'     => new sfWidgetFormFilterInput(),
      'email_address' => new sfWidgetFormFilterInput(),
      'body'          => new sfWidgetFormFilterInput(),
      'content_id'     => new sfWidgetFormDoctrineChoice(array('model' => 'Content', 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'first_name'    => new sfValidatorPass(array('required' => false)),
      'last_name'     => new sfValidatorPass(array('required' => false)),
      'email_address' => new sfValidatorPass(array('required' => false)),
      'body'          => new sfValidatorPass(array('required' => false)),
      'content_id'     => new sfValidatorDoctrineChoice(array('required' => false, 'model' => 'Content', 'column' => 'id')),
    ));

    $this->widgetSchema->setNameFormat('user_profile_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'UserProfile';
  }

  public function getFields()
  {
    return array(
      'id'            => 'Number',
      'first_name'    => 'Text',
      'last_name'     => 'Text',
      'email_address' => 'Text',
      'body'          => 'Text',
      'content_id'     => 'ForeignKey',
    );
  }
}