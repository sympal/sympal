<?php

/**
 * sfGuardPermission form base class.
 *
 * @package    form
 * @subpackage sf_guard_permission
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BasesfGuardPermissionForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'              => new sfWidgetFormInputHidden(),
      'name'            => new sfWidgetFormInput(),
      'description'     => new sfWidgetFormTextarea(),
      'created_at'      => new sfWidgetFormDateTime(),
      'updated_at'      => new sfWidgetFormDateTime(),
      'groups_list'     => new sfWidgetFormDoctrineChoiceMany(array('model' => 'sfGuardGroup')),
      'users_list'      => new sfWidgetFormDoctrineChoiceMany(array('model' => 'sfGuardUser')),
      'menu_items_list' => new sfWidgetFormDoctrineChoiceMany(array('model' => 'MenuItem')),
      'entities_list'   => new sfWidgetFormDoctrineChoiceMany(array('model' => 'Entity')),
    ));

    $this->setValidators(array(
      'id'              => new sfValidatorDoctrineChoice(array('model' => 'sfGuardPermission', 'column' => 'id', 'required' => false)),
      'name'            => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'description'     => new sfValidatorString(array('max_length' => 1000, 'required' => false)),
      'created_at'      => new sfValidatorDateTime(),
      'updated_at'      => new sfValidatorDateTime(),
      'groups_list'     => new sfValidatorDoctrineChoiceMany(array('model' => 'sfGuardGroup', 'required' => false)),
      'users_list'      => new sfValidatorDoctrineChoiceMany(array('model' => 'sfGuardUser', 'required' => false)),
      'menu_items_list' => new sfValidatorDoctrineChoiceMany(array('model' => 'MenuItem', 'required' => false)),
      'entities_list'   => new sfValidatorDoctrineChoiceMany(array('model' => 'Entity', 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'sfGuardPermission', 'column' => array('name')))
    );

    $this->widgetSchema->setNameFormat('sf_guard_permission[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'sfGuardPermission';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['groups_list']))
    {
      $this->setDefault('groups_list', $this->object->Groups->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['users_list']))
    {
      $this->setDefault('users_list', $this->object->Users->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['menu_items_list']))
    {
      $this->setDefault('menu_items_list', $this->object->MenuItems->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['entities_list']))
    {
      $this->setDefault('entities_list', $this->object->Entities->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
            $this->saveGroupsList($con);
            $this->saveUsersList($con);
            $this->saveMenuItemsList($con);
            $this->saveEntitiesList($con);
    
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

  public function saveUsersList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['users_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    $this->object->unlink('Users', array());

    $values = $this->getValue('users_list');
    if (is_array($values))
    {
      $this->object->link('Users', $values);
    }
  }

  public function saveMenuItemsList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['menu_items_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    $this->object->unlink('MenuItems', array());

    $values = $this->getValue('menu_items_list');
    if (is_array($values))
    {
      $this->object->link('MenuItems', $values);
    }
  }

  public function saveEntitiesList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['entities_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    $this->object->unlink('Entities', array());

    $values = $this->getValue('entities_list');
    if (is_array($values))
    {
      $this->object->link('Entities', $values);
    }
  }

}