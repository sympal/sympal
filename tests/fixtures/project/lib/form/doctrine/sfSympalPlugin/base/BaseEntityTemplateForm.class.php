<?php

/**
 * ContentTemplate form base class.
 *
 * @package    form
 * @subpackage content_template
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseContentTemplateForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'name'           => new sfWidgetFormInput(),
      'type'           => new sfWidgetFormChoice(array('choices' => array('View' => 'View', 'List' => 'List'))),
      'content_type_id' => new sfWidgetFormDoctrineChoice(array('model' => 'ContentType', 'add_empty' => true)),
      'partial_path'   => new sfWidgetFormInput(),
      'component_path' => new sfWidgetFormInput(),
      'body'           => new sfWidgetFormTextarea(),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorDoctrineChoice(array('model' => 'ContentTemplate', 'column' => 'id', 'required' => false)),
      'name'           => new sfValidatorString(array('max_length' => 255)),
      'type'           => new sfValidatorChoice(array('choices' => array('View' => 'View', 'List' => 'List'), 'required' => false)),
      'content_type_id' => new sfValidatorDoctrineChoice(array('model' => 'ContentType', 'required' => false)),
      'partial_path'   => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'component_path' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'body'           => new sfValidatorString(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('content_template[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'ContentTemplate';
  }

}