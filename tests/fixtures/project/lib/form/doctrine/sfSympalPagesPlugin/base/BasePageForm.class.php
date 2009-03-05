<?php

/**
 * Page form base class.
 *
 * @package    form
 * @subpackage page
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BasePageForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'entity_id'        => new sfWidgetFormDoctrineChoice(array('model' => 'Entity', 'add_empty' => false)),
      'name'             => new sfWidgetFormInput(),
      'disable_comments' => new sfWidgetFormInputCheckbox(),
      'comments_list'    => new sfWidgetFormDoctrineChoiceMany(array('model' => 'Comment')),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorDoctrineChoice(array('model' => 'Page', 'column' => 'id', 'required' => false)),
      'entity_id'        => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
      'name'             => new sfValidatorString(array('max_length' => 255)),
      'disable_comments' => new sfValidatorBoolean(array('required' => false)),
      'comments_list'    => new sfValidatorDoctrineChoiceMany(array('model' => 'Comment', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('page[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Page';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['comments_list']))
    {
      $this->setDefault('comments_list', $this->object->Comments->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
    parent::doSave($con);

    $this->saveCommentsList($con);
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