<?php

/**
 * MenuItem form base class.
 *
 * @package    form
 * @subpackage menu_item
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseMenuItemForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                => new sfWidgetFormInputHidden(),
      'site_id'           => new sfWidgetFormDoctrineChoice(array('model' => 'Site', 'add_empty' => false)),
      'entity_type_id'    => new sfWidgetFormDoctrineChoice(array('model' => 'EntityType', 'add_empty' => true)),
      'entity_id'         => new sfWidgetFormDoctrineChoice(array('model' => 'Entity', 'add_empty' => true)),
      'name'              => new sfWidgetFormInput(),
      'route'             => new sfWidgetFormInput(),
      'has_many_entities' => new sfWidgetFormInputCheckbox(),
      'requires_auth'     => new sfWidgetFormInputCheckbox(),
      'requires_no_auth'  => new sfWidgetFormInputCheckbox(),
      'is_primary'        => new sfWidgetFormInputCheckbox(),
      'is_published'      => new sfWidgetFormInputCheckbox(),
      'date_published'    => new sfWidgetFormDateTime(),
      'slug'              => new sfWidgetFormInput(),
      'root_id'           => new sfWidgetFormInput(),
      'lft'               => new sfWidgetFormInput(),
      'rgt'               => new sfWidgetFormInput(),
      'level'             => new sfWidgetFormInput(),
      'groups_list'       => new sfWidgetFormDoctrineChoiceMany(array('model' => 'sfGuardGroup')),
      'permissions_list'  => new sfWidgetFormDoctrineChoiceMany(array('model' => 'sfGuardPermission')),
    ));

    $this->setValidators(array(
      'id'                => new sfValidatorDoctrineChoice(array('model' => 'MenuItem', 'column' => 'id', 'required' => false)),
      'site_id'           => new sfValidatorDoctrineChoice(array('model' => 'Site')),
      'entity_type_id'    => new sfValidatorDoctrineChoice(array('model' => 'EntityType', 'required' => false)),
      'entity_id'         => new sfValidatorDoctrineChoice(array('model' => 'Entity', 'required' => false)),
      'name'              => new sfValidatorString(array('max_length' => 255)),
      'route'             => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'has_many_entities' => new sfValidatorBoolean(array('required' => false)),
      'requires_auth'     => new sfValidatorBoolean(array('required' => false)),
      'requires_no_auth'  => new sfValidatorBoolean(array('required' => false)),
      'is_primary'        => new sfValidatorBoolean(array('required' => false)),
      'is_published'      => new sfValidatorBoolean(array('required' => false)),
      'date_published'    => new sfValidatorDateTime(array('required' => false)),
      'slug'              => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'root_id'           => new sfValidatorInteger(array('required' => false)),
      'lft'               => new sfValidatorInteger(array('required' => false)),
      'rgt'               => new sfValidatorInteger(array('required' => false)),
      'level'             => new sfValidatorInteger(array('required' => false)),
      'groups_list'       => new sfValidatorDoctrineChoiceMany(array('model' => 'sfGuardGroup', 'required' => false)),
      'permissions_list'  => new sfValidatorDoctrineChoiceMany(array('model' => 'sfGuardPermission', 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'MenuItem', 'column' => array('slug')))
    );

    $this->widgetSchema->setNameFormat('menu_item[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'MenuItem';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['groups_list']))
    {
      $this->setDefault('groups_list', $this->object->Groups->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['permissions_list']))
    {
      $this->setDefault('permissions_list', $this->object->Permissions->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
            $this->saveGroupsList($con);
            $this->savePermissionsList($con);
    
    parent::doSave($con);
  }

  public function saveGroupsList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['groups_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    $this->object->unlink('Groups', array());

    $values = $this->getValue('groups_list');
    if (is_array($values))
    {
      $this->object->link('Groups', $values);
    }
  }

  public function savePermissionsList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['permissions_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    $this->object->unlink('Permissions', array());

    $values = $this->getValue('permissions_list');
    if (is_array($values))
    {
      $this->object->link('Permissions', $values);
    }
  }

}