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
      'title'            => new sfWidgetFormFilterInput(),
      'disable_comments' => new sfWidgetFormChoice(array('choices' => array('' => 'yes or no', 1 => 'yes', 0 => 'no'))),
      'content_id'       => new sfWidgetFormDoctrineChoice(array('model' => 'Content', 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'title'            => new sfValidatorPass(array('required' => false)),
      'disable_comments' => new sfValidatorChoice(array('required' => false, 'choices' => array('', 1, 0))),
      'content_id'       => new sfValidatorDoctrineChoice(array('required' => false, 'model' => 'Content', 'column' => 'id')),
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
      'title'            => 'Text',
      'disable_comments' => 'Boolean',
      'content_id'       => 'ForeignKey',
    );
  }
}