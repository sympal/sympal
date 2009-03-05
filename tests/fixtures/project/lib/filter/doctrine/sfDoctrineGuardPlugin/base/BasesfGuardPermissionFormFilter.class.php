<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/doctrine/BaseFormFilterDoctrine.class.php');

/**
 * sfGuardPermission filter form base class.
 *
 * @package    filters
 * @subpackage sfGuardPermission *
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class BasesfGuardPermissionFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'            => new sfWidgetFormFilterInput(),
      'description'     => new sfWidgetFormFilterInput(),
      'created_at'      => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => true)),
      'updated_at'      => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => true)),
      'groups_list'     => new sfWidgetFormDoctrineChoiceMany(array('model' => 'sfGuardGroup')),
      'users_list'      => new sfWidgetFormDoctrineChoiceMany(array('model' => 'sfGuardUser')),
      'menu_items_list' => new sfWidgetFormDoctrineChoiceMany(array('model' => 'MenuItem')),
      'entities_list'   => new sfWidgetFormDoctrineChoiceMany(array('model' => 'Entity')),
    ));

    $this->setValidators(array(
      'name'            => new sfValidatorPass(array('required' => false)),
      'description'     => new sfValidatorPass(array('required' => false)),
      'created_at'      => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDate(array('required' => false)))),
      'updated_at'      => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDate(array('required' => false)))),
      'groups_list'     => new sfValidatorDoctrineChoiceMany(array('model' => 'sfGuardGroup', 'required' => false)),
      'users_list'      => new sfValidatorDoctrineChoiceMany(array('model' => 'sfGuardUser', 'required' => false)),
      'menu_items_list' => new sfValidatorDoctrineChoiceMany(array('model' => 'MenuItem', 'required' => false)),
      'entities_list'   => new sfValidatorDoctrineChoiceMany(array('model' => 'Entity', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sf_guard_permission_filters[%s]');

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

    $query->leftJoin('r.sfGuardGroupPermission sfGuardGroupPermission')
          ->andWhereIn('sfGuardGroupPermission.group_id', $values);
  }

  public function addUsersListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.sfGuardUserPermission sfGuardUserPermission')
          ->andWhereIn('sfGuardUserPermission.user_id', $values);
  }

  public function addMenuItemsListColumnQuery(Doctrine_Query $query, $field, $values)
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
          ->andWhereIn('MenuItemPermission.menu_item_id', $values);
  }

  public function addEntitiesListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.EntityPermission EntityPermission')
          ->andWhereIn('EntityPermission.entity_id', $values);
  }

  public function getModelName()
  {
    return 'sfGuardPermission';
  }

  public function getFields()
  {
    return array(
      'id'              => 'Number',
      'name'            => 'Text',
      'description'     => 'Text',
      'created_at'      => 'Date',
      'updated_at'      => 'Date',
      'groups_list'     => 'ManyKey',
      'users_list'      => 'ManyKey',
      'menu_items_list' => 'ManyKey',
      'entities_list'   => 'ManyKey',
    );
  }
}