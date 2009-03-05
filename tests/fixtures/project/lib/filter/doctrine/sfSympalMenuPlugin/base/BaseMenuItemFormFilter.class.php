<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/doctrine/BaseFormFilterDoctrine.class.php');

/**
 * MenuItem filter form base class.
 *
 * @package    filters
 * @subpackage MenuItem *
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class BaseMenuItemFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'site_id'           => new sfWidgetFormDoctrineChoice(array('model' => 'Site', 'add_empty' => true)),
      'entity_type_id'    => new sfWidgetFormDoctrineChoice(array('model' => 'EntityType', 'add_empty' => true)),
      'entity_id'         => new sfWidgetFormDoctrineChoice(array('model' => 'Entity', 'add_empty' => true)),
      'name'              => new sfWidgetFormFilterInput(),
      'route'             => new sfWidgetFormFilterInput(),
      'has_many_entities' => new sfWidgetFormChoice(array('choices' => array('' => 'yes or no', 1 => 'yes', 0 => 'no'))),
      'requires_auth'     => new sfWidgetFormChoice(array('choices' => array('' => 'yes or no', 1 => 'yes', 0 => 'no'))),
      'requires_no_auth'  => new sfWidgetFormChoice(array('choices' => array('' => 'yes or no', 1 => 'yes', 0 => 'no'))),
      'is_primary'        => new sfWidgetFormChoice(array('choices' => array('' => 'yes or no', 1 => 'yes', 0 => 'no'))),
      'is_published'      => new sfWidgetFormChoice(array('choices' => array('' => 'yes or no', 1 => 'yes', 0 => 'no'))),
      'date_published'    => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => true)),
      'root_id'           => new sfWidgetFormFilterInput(),
      'lft'               => new sfWidgetFormFilterInput(),
      'rgt'               => new sfWidgetFormFilterInput(),
      'level'             => new sfWidgetFormFilterInput(),
      'groups_list'       => new sfWidgetFormDoctrineChoiceMany(array('model' => 'sfGuardGroup')),
      'permissions_list'  => new sfWidgetFormDoctrineChoiceMany(array('model' => 'sfGuardPermission')),
    ));

    $this->setValidators(array(
      'site_id'           => new sfValidatorDoctrineChoice(array('required' => false, 'model' => 'Site', 'column' => 'id')),
      'entity_type_id'    => new sfValidatorDoctrineChoice(array('required' => false, 'model' => 'EntityType', 'column' => 'id')),
      'entity_id'         => new sfValidatorDoctrineChoice(array('required' => false, 'model' => 'Entity', 'column' => 'id')),
      'name'              => new sfValidatorPass(array('required' => false)),
      'route'             => new sfValidatorPass(array('required' => false)),
      'has_many_entities' => new sfValidatorChoice(array('required' => false, 'choices' => array('', 1, 0))),
      'requires_auth'     => new sfValidatorChoice(array('required' => false, 'choices' => array('', 1, 0))),
      'requires_no_auth'  => new sfValidatorChoice(array('required' => false, 'choices' => array('', 1, 0))),
      'is_primary'        => new sfValidatorChoice(array('required' => false, 'choices' => array('', 1, 0))),
      'is_published'      => new sfValidatorChoice(array('required' => false, 'choices' => array('', 1, 0))),
      'date_published'    => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDate(array('required' => false)))),
      'root_id'           => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'lft'               => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'rgt'               => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'level'             => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'groups_list'       => new sfValidatorDoctrineChoiceMany(array('model' => 'sfGuardGroup', 'required' => false)),
      'permissions_list'  => new sfValidatorDoctrineChoiceMany(array('model' => 'sfGuardPermission', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('menu_item_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

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

    $query->leftJoin('r.MenuItemGroup MenuItemGroup')
          ->andWhereIn('MenuItemGroup.group_id', $values);
  }

  public function addPermissionsListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.MenuItemPermission MenuItemPermission')
          ->andWhereIn('MenuItemPermission.permission_id', $values);
  }

  public function getModelName()
  {
    return 'MenuItem';
  }

  public function getFields()
  {
    return array(
      'id'                => 'Number',
      'site_id'           => 'ForeignKey',
      'entity_type_id'    => 'ForeignKey',
      'entity_id'         => 'ForeignKey',
      'name'              => 'Text',
      'route'             => 'Text',
      'has_many_entities' => 'Boolean',
      'requires_auth'     => 'Boolean',
      'requires_no_auth'  => 'Boolean',
      'is_primary'        => 'Boolean',
      'is_published'      => 'Boolean',
      'date_published'    => 'Date',
      'root_id'           => 'Number',
      'lft'               => 'Number',
      'rgt'               => 'Number',
      'level'             => 'Number',
      'groups_list'       => 'ManyKey',
      'permissions_list'  => 'ManyKey',
    );
  }
}