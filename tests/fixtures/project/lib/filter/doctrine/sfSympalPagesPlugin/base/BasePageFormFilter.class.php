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
      'name'             => new sfWidgetFormFilterInput(),
      'disable_comments' => new sfWidgetFormChoice(array('choices' => array('' => 'yes or no', 1 => 'yes', 0 => 'no'))),
      'comments_list'    => new sfWidgetFormDoctrineChoiceMany(array('model' => 'Comment')),
    ));

    $this->setValidators(array(
      'entity_id'        => new sfValidatorDoctrineChoice(array('required' => false, 'model' => 'Entity', 'column' => 'id')),
      'name'             => new sfValidatorPass(array('required' => false)),
      'disable_comments' => new sfValidatorChoice(array('required' => false, 'choices' => array('', 1, 0))),
      'comments_list'    => new sfValidatorDoctrineChoiceMany(array('model' => 'Comment', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('page_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function addCommentsListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.PageComment PageComment')
          ->andWhereIn('PageComment.comment_id', $values);
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
      'name'             => 'Text',
      'disable_comments' => 'Boolean',
      'comments_list'    => 'ManyKey',
    );
  }
}