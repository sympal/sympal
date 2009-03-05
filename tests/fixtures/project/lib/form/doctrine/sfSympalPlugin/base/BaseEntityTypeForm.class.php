<?php

/**
 * EntityType form base class.
 *
 * @package    form
 * @subpackage entity_type
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseEntityTypeForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'name'           => new sfWidgetFormInput(),
      'label'          => new sfWidgetFormInput(),
      'list_route_url' => new sfWidgetFormInput(),
      'view_route_url' => new sfWidgetFormInput(),
      'layout'         => new sfWidgetFormInput(),
      'slug'           => new sfWidgetFormInput(),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorDoctrineChoice(array('model' => 'EntityType', 'column' => 'id', 'required' => false)),
      'name'           => new sfValidatorString(array('max_length' => 255)),
      'label'          => new sfValidatorString(array('max_length' => 255)),
      'list_route_url' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'view_route_url' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'layout'         => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'slug'           => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'EntityType', 'column' => array('slug')))
    );

    $this->widgetSchema->setNameFormat('entity_type[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'EntityType';
  }

}