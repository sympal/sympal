<?php

/**
 * sfSympalMenuItem form base class.
 *
 * @method sfSympalMenuItem getObject() Returns the current form's model object
 *
 * @package    sympal
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasesfSympalMenuItemForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'name'             => new sfWidgetFormInputText(),
      'root_id'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('RootMenuItem'), 'add_empty' => true)),
      'date_published'   => new sfWidgetFormDateTime(),
      'label'            => new sfWidgetFormInputText(),
      'custom_path'      => new sfWidgetFormInputText(),
      'requires_auth'    => new sfWidgetFormInputCheckbox(),
      'requires_no_auth' => new sfWidgetFormInputCheckbox(),
      'html_attributes'  => new sfWidgetFormInputText(),
      'site_id'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Site'), 'add_empty' => false)),
      'content_id'       => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('RelatedContent'), 'add_empty' => true)),
      'slug'             => new sfWidgetFormInputText(),
      'lft'              => new sfWidgetFormInputText(),
      'rgt'              => new sfWidgetFormInputText(),
      'level'            => new sfWidgetFormInputText(),
      'groups_list'      => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardGroup')),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'name'             => new sfValidatorString(array('max_length' => 255)),
      'root_id'          => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('RootMenuItem'), 'required' => false)),
      'date_published'   => new sfValidatorDateTime(array('required' => false)),
      'label'            => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'custom_path'      => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'requires_auth'    => new sfValidatorBoolean(array('required' => false)),
      'requires_no_auth' => new sfValidatorBoolean(array('required' => false)),
      'html_attributes'  => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'site_id'          => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Site'))),
      'content_id'       => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('RelatedContent'), 'required' => false)),
      'slug'             => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'lft'              => new sfValidatorInteger(array('required' => false)),
      'rgt'              => new sfValidatorInteger(array('required' => false)),
      'level'            => new sfValidatorInteger(array('required' => false)),
      'groups_list'      => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardGroup', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sf_sympal_menu_item[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'sfSympalMenuItem';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['groups_list']))
    {
      $this->setDefault('groups_list', $this->object->Groups->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
    $this->saveGroupsList($con);

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

}
