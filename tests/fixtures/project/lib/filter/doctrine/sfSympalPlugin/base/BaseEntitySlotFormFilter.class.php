<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/doctrine/BaseFormFilterDoctrine.class.php');

/**
 * EntitySlot filter form base class.
 *
 * @package    filters
 * @subpackage EntitySlot *
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class BaseEntitySlotFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'entity_id'           => new sfWidgetFormDoctrineChoice(array('model' => 'Entity', 'add_empty' => true)),
      'entity_slot_type_id' => new sfWidgetFormDoctrineChoice(array('model' => 'EntitySlotType', 'add_empty' => true)),
      'name'                => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'entity_id'           => new sfValidatorDoctrineChoice(array('required' => false, 'model' => 'Entity', 'column' => 'id')),
      'entity_slot_type_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => 'EntitySlotType', 'column' => 'id')),
      'name'                => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('entity_slot_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'EntitySlot';
  }

  public function getFields()
  {
    return array(
      'id'                  => 'Number',
      'entity_id'           => 'ForeignKey',
      'entity_slot_type_id' => 'ForeignKey',
      'name'                => 'Text',
    );
  }
}