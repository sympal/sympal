<?php

/**
 * Group form base class.
 *
 * @package    form
 * @subpackage group
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseGroupForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'name'             => new sfWidgetFormInput(),
      'description'      => new sfWidgetFormTextarea(),
      'created_at'       => new sfWidgetFormDateTime(),
      'updated_at'       => new sfWidgetFormDateTime(),
      'users_list'       => new sfWidgetFormDoctrineChoiceMany(array('model' => 'User')),
      'permissions_list' => new sfWidgetFormDoctrineChoiceMany(array('model' => 'Permission')),
      'menu_items_list'  => new sfWidgetFormDoctrineChoiceMany(array('model' => 'MenuItem')),
      'content_list'     => new sfWidgetFormDoctrineChoiceMany(array('model' => 'Content')),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorDoctrineChoice(array('model' => 'Group', 'column' => 'id', 'required' => false)),
      'name'             => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'description'      => new sfValidatorString(array('max_length' => 1000, 'required' => false)),
      'created_at'       => new sfValidatorDateTime(array('required' => false)),
      'updated_at'       => new sfValidatorDateTime(array('required' => false)),
      'users_list'       => new sfValidatorDoctrineChoiceMany(array('model' => 'User', 'required' => false)),
      'permissions_list' => new sfValidatorDoctrineChoiceMany(array('model' => 'Permission', 'required' => false)),
      'menu_items_list'  => new sfValidatorDoctrineChoiceMany(array('model' => 'MenuItem', 'required' => false)),
      'content_list'     => new sfValidatorDoctrineChoiceMany(array('model' => 'Content', 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'Group', 'column' => array('name')))
    );

    $this->widgetSchema->setNameFormat('group[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Group';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['users_list']))
    {
      $this->setDefault('users_list', $this->object->Users->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['permissions_list']))
    {
      $this->setDefault('permissions_list', $this->object->Permissions->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['menu_items_list']))
    {
      $this->setDefault('menu_items_list', $this->object->MenuItems->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['content_list']))
    {
      $this->setDefault('content_list', $this->object->Content->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
    parent::doSave($con);

    $this->saveUsersList($con);
    $this->savePermissionsList($con);
    $this->saveMenuItemsList($con);
    $this->saveContentList($con);
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

  public function saveContentList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['content_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    $this->object->unlink('Content', array());

    $values = $this->getValue('content_list');
    if (is_array($values))
    {
      $this->object->link('Content', $values);
    }
  }

}