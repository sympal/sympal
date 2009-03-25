<?php

/**
 * ContentType form base class.
 *
 * @package    form
 * @subpackage content_type
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseContentTypeForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'name'           => new sfWidgetFormInput(),
      'label'          => new sfWidgetFormInput(),
      'list_path' => new sfWidgetFormInput(),
      'view_path' => new sfWidgetFormInput(),
      'layout'         => new sfWidgetFormInput(),
      'slug'           => new sfWidgetFormInput(),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorDoctrineChoice(array('model' => 'ContentType', 'column' => 'id', 'required' => false)),
      'name'           => new sfValidatorString(array('max_length' => 255)),
      'label'          => new sfValidatorString(array('max_length' => 255)),
      'list_path' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'view_path' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'layout'         => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'slug'           => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'ContentType', 'column' => array('slug')))
    );

    $this->widgetSchema->setNameFormat('content_type[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'ContentType';
  }

}