<?php

/**
 * Comment form base class.
 *
 * @package    form
 * @subpackage comment
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseCommentForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'status'       => new sfWidgetFormChoice(array('choices' => array('Pending' => 'Pending', 'Approved' => 'Approved', 'Denied' => 'Denied'))),
      'user_id'      => new sfWidgetFormDoctrineChoice(array('model' => 'User', 'add_empty' => true)),
      'name'         => new sfWidgetFormInput(),
      'subject'      => new sfWidgetFormInput(),
      'body'         => new sfWidgetFormTextarea(),
      'created_at'   => new sfWidgetFormDateTime(),
      'updated_at'   => new sfWidgetFormDateTime(),
      'content_list' => new sfWidgetFormDoctrineChoiceMany(array('model' => 'Content')),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorDoctrineChoice(array('model' => 'Comment', 'column' => 'id', 'required' => false)),
      'status'       => new sfValidatorChoice(array('choices' => array('Pending' => 'Pending', 'Approved' => 'Approved', 'Denied' => 'Denied'))),
      'user_id'      => new sfValidatorDoctrineChoice(array('model' => 'User', 'required' => false)),
      'name'         => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'subject'      => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'body'         => new sfValidatorString(),
      'created_at'   => new sfValidatorDateTime(array('required' => false)),
      'updated_at'   => new sfValidatorDateTime(array('required' => false)),
      'content_list' => new sfValidatorDoctrineChoiceMany(array('model' => 'Content', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('comment[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Comment';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['content_list']))
    {
      $this->setDefault('content_list', $this->object->Content->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
    parent::doSave($con);

    $this->saveContentList($con);
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