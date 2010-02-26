<?php

/**
 * sfSympalContentList filter form base class.
 *
 * @package    sympal
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasesfSympalContentListFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'title'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'content_type_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ContentType'), 'add_empty' => true)),
      'rows_per_page'   => new sfWidgetFormFilterInput(),
      'sort_column'     => new sfWidgetFormFilterInput(),
      'sort_order'      => new sfWidgetFormChoice(array('choices' => array('' => '', 'ASC' => 'ASC', 'DESC' => 'DESC'))),
      'table_method'    => new sfWidgetFormFilterInput(),
      'dql_query'       => new sfWidgetFormFilterInput(),
      'content_id'      => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Content'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'title'           => new sfValidatorPass(array('required' => false)),
      'content_type_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('ContentType'), 'column' => 'id')),
      'rows_per_page'   => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'sort_column'     => new sfValidatorPass(array('required' => false)),
      'sort_order'      => new sfValidatorChoice(array('required' => false, 'choices' => array('ASC' => 'ASC', 'DESC' => 'DESC'))),
      'table_method'    => new sfValidatorPass(array('required' => false)),
      'dql_query'       => new sfValidatorPass(array('required' => false)),
      'content_id'      => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Content'), 'column' => 'id')),
    ));

    $this->widgetSchema->setNameFormat('sf_sympal_content_list_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'sfSympalContentList';
  }

  public function getFields()
  {
    return array(
      'id'              => 'Number',
      'title'           => 'Text',
      'content_type_id' => 'ForeignKey',
      'rows_per_page'   => 'Number',
      'sort_column'     => 'Text',
      'sort_order'      => 'Enum',
      'table_method'    => 'Text',
      'dql_query'       => 'Text',
      'content_id'      => 'ForeignKey',
    );
  }
}
