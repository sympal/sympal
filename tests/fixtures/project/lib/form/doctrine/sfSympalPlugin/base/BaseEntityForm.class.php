<?php

/**
 * Entity form base class.
 *
 * @package    form
 * @subpackage entity
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseEntityForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                  => new sfWidgetFormInputHidden(),
      'site_id'             => new sfWidgetFormDoctrineChoice(array('model' => 'Site', 'add_empty' => false)),
      'entity_type_id'      => new sfWidgetFormDoctrineChoice(array('model' => 'EntityType', 'add_empty' => false)),
      'entity_template_id'  => new sfWidgetFormDoctrineChoice(array('model' => 'EntityTemplate', 'add_empty' => true)),
      'master_menu_item_id' => new sfWidgetFormDoctrineChoice(array('model' => 'MenuItem', 'add_empty' => true)),
      'last_updated_by'     => new sfWidgetFormDoctrineChoice(array('model' => 'sfGuardUser', 'add_empty' => true)),
      'created_by'          => new sfWidgetFormDoctrineChoice(array('model' => 'sfGuardUser', 'add_empty' => true)),
      'locked_by'           => new sfWidgetFormDoctrineChoice(array('model' => 'sfGuardUser', 'add_empty' => true)),
      'is_published'        => new sfWidgetFormInputCheckbox(),
      'date_published'      => new sfWidgetFormDateTime(),
      'custom_path'         => new sfWidgetFormInput(),
      'layout'              => new sfWidgetFormInput(),
      'slug'                => new sfWidgetFormInput(),
      'created_at'          => new sfWidgetFormDateTime(),
      'updated_at'          => new sfWidgetFormDateTime(),
      'groups_list'         => new sfWidgetFormDoctrineChoiceMany(array('model' => 'sfGuardGroup')),
      'permissions_list'    => new sfWidgetFormDoctrineChoiceMany(array('model' => 'sfGuardPermission')),
      'comments_list'       => new sfWidgetFormDoctrineChoiceMany(array('model' => 'Comment')),
    ));

    $this->setValidators(array(
      'id'                  => new sfValidatorDoctrineChoice(array('model' => 'Entity', 'column' => 'id', 'required' => false)),
      'site_id'             => new sfValidatorDoctrineChoice(array('model' => 'Site')),
      'entity_type_id'      => new sfValidatorDoctrineChoice(array('model' => 'EntityType')),
      'entity_template_id'  => new sfValidatorDoctrineChoice(array('model' => 'EntityTemplate', 'required' => false)),
      'master_menu_item_id' => new sfValidatorDoctrineChoice(array('model' => 'MenuItem', 'required' => false)),
      'last_updated_by'     => new sfValidatorDoctrineChoice(array('model' => 'sfGuardUser', 'required' => false)),
      'created_by'          => new sfValidatorDoctrineChoice(array('model' => 'sfGuardUser', 'required' => false)),
      'locked_by'           => new sfValidatorDoctrineChoice(array('model' => 'sfGuardUser', 'required' => false)),
      'is_published'        => new sfValidatorBoolean(array('required' => false)),
      'date_published'      => new sfValidatorDateTime(array('required' => false)),
      'custom_path'         => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'layout'              => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'slug'                => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'created_at'          => new sfValidatorDateTime(),
      'updated_at'          => new sfValidatorDateTime(),
      'groups_list'         => new sfValidatorDoctrineChoiceMany(array('model' => 'sfGuardGroup', 'required' => false)),
      'permissions_list'    => new sfValidatorDoctrineChoiceMany(array('model' => 'sfGuardPermission', 'required' => false)),
      'comments_list'       => new sfValidatorDoctrineChoiceMany(array('model' => 'Comment', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('entity[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Entity';
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

    if (isset($this->widgetSchema['comments_list']))
    {
      $this->setDefault('comments_list', $this->object->Comments->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
            $this->saveGroupsList($con);
            $this->savePermissionsList($con);
            $this->saveCommentsList($con);
    
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

  public function saveCommentsList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['comments_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    $this->object->unlink('Comments', array());

    $values = $this->getValue('comments_list');
    if (is_array($values))
    {
      $this->object->link('Comments', $values);
    }
  }

}