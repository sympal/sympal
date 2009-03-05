<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/doctrine/BaseFormFilterDoctrine.class.php');

/**
 * EntityTemplate filter form base class.
 *
 * @package    filters
 * @subpackage EntityTemplate *
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class BaseEntityTemplateFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'           => new sfWidgetFormFilterInput(),
      'type'           => new sfWidgetFormChoice(array('choices' => array('' => '', 'View' => 'View', 'List' => 'List'))),
      'entity_type_id' => new sfWidgetFormDoctrineChoice(array('model' => 'EntityType', 'add_empty' => true)),
      'partial_path'   => new sfWidgetFormFilterInput(),
      'component_path' => new sfWidgetFormFilterInput(),
      'body'           => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'name'           => new sfValidatorPass(array('required' => false)),
      'type'           => new sfValidatorChoice(array('required' => false, 'choices' => array('View' => 'View', 'List' => 'List'))),
      'entity_type_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => 'EntityType', 'column' => 'id')),
      'partial_path'   => new sfValidatorPass(array('required' => false)),
      'component_path' => new sfValidatorPass(array('required' => false)),
      'body'           => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('entity_template_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'EntityTemplate';
  }

  public function getFields()
  {
    return array(
      'id'             => 'Number',
      'name'           => 'Text',
      'type'           => 'Enum',
      'entity_type_id' => 'ForeignKey',
      'partial_path'   => 'Text',
      'component_path' => 'Text',
      'body'           => 'Text',
    );
  }
}