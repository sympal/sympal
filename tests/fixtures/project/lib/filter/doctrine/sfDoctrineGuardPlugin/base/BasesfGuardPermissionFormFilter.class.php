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
      'created_at'      => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'      => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'groups_list'     => new sfWidgetFormDoctrineChoiceMany(array('model' => 'sfGuardGroup')),
      'users_list'      => new sfWidgetFormDoctrineChoiceMany(array('model' => 'sfGuardUser')),
      'menu_items_list' => new sfWidgetFormDoctrineChoiceMany(array('model' => 'MenuItem')),
      'content_list'    => new sfWidgetFormDoctrineChoiceMany(array('model' => 'Content')),
    ));

    $this->setValidators(array(
      'name'            => new sfValidatorPass(array('required' => false)),
      'description'     => new sfValidatorPass(array('required' => false)),
      'created_at'      => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDate(array('required' => false)))),
      'updated_at'      => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDate(array('required' => false)))),
      'groups_list'     => new sfValidatorDoctrineChoiceMany(array('model' => 'sfGuardGroup', 'required' => false)),
      'users_list'      => new sfValidatorDoctrineChoiceMany(array('model' => 'sfGuardUser', 'required' => false)),
      'menu_items_list' => new sfValidatorDoctrineChoiceMany(array('model' => 'MenuItem', 'required' => false)),
      'content_list'    => new sfValidatorDoctrineChoiceMany(array('model' => 'Content', 'required' => false)),
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

    $query->leftJoin('r.ContentPermission ContentPermission')
          ->andWhereIn('ContentPermission.content_id', $values);
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
      'content_list'    => 'ManyKey',
    );
  }
}