<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/doctrine/BaseFormFilterDoctrine.class.php');

/**
 * ContentType filter form base class.
 *
 * @package    filters
 * @subpackage ContentType *
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class BaseContentTypeFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'      => new sfWidgetFormFilterInput(),
      'label'     => new sfWidgetFormFilterInput(),
      'list_path' => new sfWidgetFormFilterInput(),
      'view_path' => new sfWidgetFormFilterInput(),
      'layout'    => new sfWidgetFormFilterInput(),
      'slug'      => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'name'      => new sfValidatorPass(array('required' => false)),
      'label'     => new sfValidatorPass(array('required' => false)),
      'list_path' => new sfValidatorPass(array('required' => false)),
      'view_path' => new sfValidatorPass(array('required' => false)),
      'layout'    => new sfValidatorPass(array('required' => false)),
      'slug'      => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('content_type_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'ContentType';
  }

  public function getFields()
  {
    return array(
      'id'        => 'Number',
      'name'      => 'Text',
      'label'     => 'Text',
      'list_path' => 'Text',
      'view_path' => 'Text',
      'layout'    => 'Text',
      'slug'      => 'Text',
    );
  }
}