<?php

/**
 * sfSympalContentList form base class.
 *
 * @method sfSympalContentList getObject() Returns the current form's model object
 *
 * @package    sympal
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasesfSympalContentListForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'              => new sfWidgetFormInputHidden(),
      'title'           => new sfWidgetFormInputText(),
      'content_type_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ContentType'), 'add_empty' => false)),
      'rows_per_page'   => new sfWidgetFormInputText(),
      'sort_column'     => new sfWidgetFormInputText(),
      'sort_order'      => new sfWidgetFormChoice(array('choices' => array('ASC' => 'ASC', 'DESC' => 'DESC'))),
      'table_method'    => new sfWidgetFormInputText(),
      'dql_query'       => new sfWidgetFormTextarea(),
      'content_id'      => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Content'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'id'              => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'title'           => new sfValidatorString(array('max_length' => 255)),
      'content_type_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ContentType'))),
      'rows_per_page'   => new sfValidatorInteger(array('required' => false)),
      'sort_column'     => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'sort_order'      => new sfValidatorChoice(array('choices' => array(0 => 'ASC', 1 => 'DESC'), 'required' => false)),
      'table_method'    => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'dql_query'       => new sfValidatorString(array('required' => false)),
      'content_id'      => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Content'), 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sf_sympal_content_list[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'sfSympalContentList';
  }

}
