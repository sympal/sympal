<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/doctrine/BaseFormFilterDoctrine.class.php');

/**
 * Comment filter form base class.
 *
 * @package    filters
 * @subpackage Comment *
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class BaseCommentFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'status'       => new sfWidgetFormChoice(array('choices' => array('' => '', 'Pending' => 'Pending', 'Approved' => 'Approved', 'Denied' => 'Denied'))),
      'user_id'      => new sfWidgetFormDoctrineChoice(array('model' => 'User', 'add_empty' => true)),
      'name'         => new sfWidgetFormFilterInput(),
      'subject'      => new sfWidgetFormFilterInput(),
      'body'         => new sfWidgetFormFilterInput(),
      'created_at'   => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => true)),
      'updated_at'   => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => true)),
      'content_list' => new sfWidgetFormDoctrineChoiceMany(array('model' => 'Content')),
    ));

    $this->setValidators(array(
      'status'       => new sfValidatorChoice(array('required' => false, 'choices' => array('Pending' => 'Pending', 'Approved' => 'Approved', 'Denied' => 'Denied'))),
      'user_id'      => new sfValidatorDoctrineChoice(array('required' => false, 'model' => 'User', 'column' => 'id')),
      'name'         => new sfValidatorPass(array('required' => false)),
      'subject'      => new sfValidatorPass(array('required' => false)),
      'body'         => new sfValidatorPass(array('required' => false)),
      'created_at'   => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDate(array('required' => false)))),
      'updated_at'   => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDate(array('required' => false)))),
      'content_list' => new sfValidatorDoctrineChoiceMany(array('model' => 'Content', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('comment_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

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

    $query->leftJoin('r.ContentComment ContentComment')
          ->andWhereIn('ContentComment.content_id', $values);
  }

  public function getModelName()
  {
    return 'Comment';
  }

  public function getFields()
  {
    return array(
      'id'           => 'Number',
      'status'       => 'Enum',
      'user_id'      => 'ForeignKey',
      'name'         => 'Text',
      'subject'      => 'Text',
      'body'         => 'Text',
      'created_at'   => 'Date',
      'updated_at'   => 'Date',
      'content_list' => 'ManyKey',
    );
  }
}