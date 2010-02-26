<?php

/**
 * sfSympalMenuItem filter form base class.
 *
 * @package    sympal
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasesfSympalMenuItemFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'root_id'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('RootMenuItem'), 'add_empty' => true)),
      'date_published'   => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
      'label'            => new sfWidgetFormFilterInput(),
      'custom_path'      => new sfWidgetFormFilterInput(),
      'requires_auth'    => new sfWidgetFormChoice(array('choices' => array('' => 'yes or no', 1 => 'yes', 0 => 'no'))),
      'requires_no_auth' => new sfWidgetFormChoice(array('choices' => array('' => 'yes or no', 1 => 'yes', 0 => 'no'))),
      'html_attributes'  => new sfWidgetFormFilterInput(),
      'site_id'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Site'), 'add_empty' => true)),
      'content_id'       => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('RelatedContent'), 'add_empty' => true)),
      'slug'             => new sfWidgetFormFilterInput(),
      'lft'              => new sfWidgetFormFilterInput(),
      'rgt'              => new sfWidgetFormFilterInput(),
      'level'            => new sfWidgetFormFilterInput(),
      'groups_list'      => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardGroup')),
    ));

    $this->setValidators(array(
      'name'             => new sfValidatorPass(array('required' => false)),
      'root_id'          => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('RootMenuItem'), 'column' => 'id')),
      'date_published'   => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'label'            => new sfValidatorPass(array('required' => false)),
      'custom_path'      => new sfValidatorPass(array('required' => false)),
      'requires_auth'    => new sfValidatorChoice(array('required' => false, 'choices' => array('', 1, 0))),
      'requires_no_auth' => new sfValidatorChoice(array('required' => false, 'choices' => array('', 1, 0))),
      'html_attributes'  => new sfValidatorPass(array('required' => false)),
      'site_id'          => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Site'), 'column' => 'id')),
      'content_id'       => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('RelatedContent'), 'column' => 'id')),
      'slug'             => new sfValidatorPass(array('required' => false)),
      'lft'              => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'rgt'              => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'level'            => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'groups_list'      => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardGroup', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sf_sympal_menu_item_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function addGroupsListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.sfSympalMenuItemGroup sfSympalMenuItemGroup')
          ->andWhereIn('sfSympalMenuItemGroup.group_id', $values);
  }

  public function getModelName()
  {
    return 'sfSympalMenuItem';
  }

  public function getFields()
  {
    return array(
      'id'               => 'Number',
      'name'             => 'Text',
      'root_id'          => 'ForeignKey',
      'date_published'   => 'Date',
      'label'            => 'Text',
      'custom_path'      => 'Text',
      'requires_auth'    => 'Boolean',
      'requires_no_auth' => 'Boolean',
      'html_attributes'  => 'Text',
      'site_id'          => 'ForeignKey',
      'content_id'       => 'ForeignKey',
      'slug'             => 'Text',
      'lft'              => 'Number',
      'rgt'              => 'Number',
      'level'            => 'Number',
      'groups_list'      => 'ManyKey',
    );
  }
}
