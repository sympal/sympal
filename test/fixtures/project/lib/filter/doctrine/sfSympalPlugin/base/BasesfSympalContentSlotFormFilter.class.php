<?php

/**
 * sfSympalContentSlot filter form base class.
 *
 * @package    sympal
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasesfSympalContentSlotFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'is_column'       => new sfWidgetFormChoice(array('choices' => array('' => 'yes or no', 1 => 'yes', 0 => 'no'))),
      'render_function' => new sfWidgetFormFilterInput(),
      'name'            => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'type'            => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'value'           => new sfWidgetFormFilterInput(),
      'content_list'    => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalContent')),
    ));

    $this->setValidators(array(
      'is_column'       => new sfValidatorChoice(array('required' => false, 'choices' => array('', 1, 0))),
      'render_function' => new sfValidatorPass(array('required' => false)),
      'name'            => new sfValidatorPass(array('required' => false)),
      'type'            => new sfValidatorPass(array('required' => false)),
      'value'           => new sfValidatorPass(array('required' => false)),
      'content_list'    => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalContent', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sf_sympal_content_slot_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function addContentListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.sfSympalContentSlotRef sfSympalContentSlotRef')
          ->andWhereIn('sfSympalContentSlotRef.content_id', $values);
  }

  public function getModelName()
  {
    return 'sfSympalContentSlot';
  }

  public function getFields()
  {
    return array(
      'id'              => 'Number',
      'is_column'       => 'Boolean',
      'render_function' => 'Text',
      'name'            => 'Text',
      'type'            => 'Text',
      'value'           => 'Text',
      'content_list'    => 'ManyKey',
    );
  }
}
