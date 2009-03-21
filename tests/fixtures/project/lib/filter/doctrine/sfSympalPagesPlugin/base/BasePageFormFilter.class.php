<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/doctrine/BaseFormFilterDoctrine.class.php');

/**
 * Page filter form base class.
 *
 * @package    filters
 * @subpackage Page *
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class BasePageFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'entity_id'        => new sfWidgetFormDoctrineChoice(array('model' => 'Entity', 'add_empty' => true)),
      'title'            => new sfWidgetFormFilterInput(),
      'disable_comments' => new sfWidgetFormChoice(array('choices' => array('' => 'yes or no', 1 => 'yes', 0 => 'no'))),
    ));

    $this->setValidators(array(
      'entity_id'        => new sfValidatorDoctrineChoice(array('required' => false, 'model' => 'Entity', 'column' => 'id')),
      'title'            => new sfValidatorPass(array('required' => false)),
      'disable_comments' => new sfValidatorChoice(array('required' => false, 'choices' => array('', 1, 0))),
    ));

    $this->widgetSchema->setNameFormat('page_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Page';
  }

  public function getFields()
  {
    return array(
      'id'               => 'Number',
      'entity_id'        => 'ForeignKey',
      'title'            => 'Text',
      'disable_comments' => 'Boolean',
    );
  }
}