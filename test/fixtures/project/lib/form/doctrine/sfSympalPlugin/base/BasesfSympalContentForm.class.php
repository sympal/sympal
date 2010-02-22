<?php

/**
 * sfSympalContent form base class.
 *
 * @method sfSympalContent getObject() Returns the current form's model object
 *
 * @package    sympal
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasesfSympalContentForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                 => new sfWidgetFormInputHidden(),
      'site_id'            => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Site'), 'add_empty' => false)),
      'content_type_id'    => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Type'), 'add_empty' => false)),
      'last_updated_by_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('LastUpdatedBy'), 'add_empty' => true)),
      'created_by_id'      => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('CreatedBy'), 'add_empty' => true)),
      'date_published'     => new sfWidgetFormDateTime(),
      'custom_path'        => new sfWidgetFormInputText(),
      'theme'              => new sfWidgetFormInputText(),
      'template'           => new sfWidgetFormInputText(),
      'module'             => new sfWidgetFormInputText(),
      'action'             => new sfWidgetFormInputText(),
      'publicly_editable'  => new sfWidgetFormInputCheckbox(),
      'page_title'         => new sfWidgetFormInputText(),
      'meta_keywords'      => new sfWidgetFormTextarea(),
      'meta_description'   => new sfWidgetFormTextarea(),
      'i18n_slug'          => new sfWidgetFormInputText(),
      'created_at'         => new sfWidgetFormDateTime(),
      'updated_at'         => new sfWidgetFormDateTime(),
      'slug'               => new sfWidgetFormInputText(),
      'slots_list'         => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalContentSlot')),
      'groups_list'        => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardGroup')),
      'edit_groups_list'   => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardGroup')),
      'links_list'         => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalContent')),
      'assets_list'        => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalAsset')),
      'comments_list'      => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalComment')),
    ));

    $this->setValidators(array(
      'id'                 => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'site_id'            => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Site'))),
      'content_type_id'    => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Type'))),
      'last_updated_by_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('LastUpdatedBy'), 'required' => false)),
      'created_by_id'      => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('CreatedBy'), 'required' => false)),
      'date_published'     => new sfValidatorDateTime(array('required' => false)),
      'custom_path'        => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'theme'              => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'template'           => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'module'             => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'action'             => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'publicly_editable'  => new sfValidatorBoolean(array('required' => false)),
      'page_title'         => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'meta_keywords'      => new sfValidatorString(array('max_length' => 500, 'required' => false)),
      'meta_description'   => new sfValidatorString(array('max_length' => 500, 'required' => false)),
      'i18n_slug'          => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'created_at'         => new sfValidatorDateTime(),
      'updated_at'         => new sfValidatorDateTime(),
      'slug'               => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'slots_list'         => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalContentSlot', 'required' => false)),
      'groups_list'        => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardGroup', 'required' => false)),
      'edit_groups_list'   => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardGroup', 'required' => false)),
      'links_list'         => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalContent', 'required' => false)),
      'assets_list'        => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalAsset', 'required' => false)),
      'comments_list'      => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalComment', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sf_sympal_content[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'sfSympalContent';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['slots_list']))
    {
      $this->setDefault('slots_list', $this->object->Slots->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['groups_list']))
    {
      $this->setDefault('groups_list', $this->object->Groups->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['edit_groups_list']))
    {
      $this->setDefault('edit_groups_list', $this->object->EditGroups->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['links_list']))
    {
      $this->setDefault('links_list', $this->object->Links->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['assets_list']))
    {
      $this->setDefault('assets_list', $this->object->Assets->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['comments_list']))
    {
      $this->setDefault('comments_list', $this->object->Comments->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
    $this->saveSlotsList($con);
    $this->saveGroupsList($con);
    $this->saveEditGroupsList($con);
    $this->saveLinksList($con);
    $this->saveAssetsList($con);
    $this->saveCommentsList($con);

    parent::doSave($con);
  }

  public function saveSlotsList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['slots_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->Slots->getPrimaryKeys();
    $values = $this->getValue('slots_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Slots', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Slots', array_values($link));
    }
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

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->Groups->getPrimaryKeys();
    $values = $this->getValue('groups_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Groups', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Groups', array_values($link));
    }
  }

  public function saveEditGroupsList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['edit_groups_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->EditGroups->getPrimaryKeys();
    $values = $this->getValue('edit_groups_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('EditGroups', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('EditGroups', array_values($link));
    }
  }

  public function saveLinksList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['links_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->Links->getPrimaryKeys();
    $values = $this->getValue('links_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Links', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Links', array_values($link));
    }
  }

  public function saveAssetsList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['assets_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->Assets->getPrimaryKeys();
    $values = $this->getValue('assets_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Assets', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Assets', array_values($link));
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

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->Comments->getPrimaryKeys();
    $values = $this->getValue('comments_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Comments', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Comments', array_values($link));
    }
  }

}
