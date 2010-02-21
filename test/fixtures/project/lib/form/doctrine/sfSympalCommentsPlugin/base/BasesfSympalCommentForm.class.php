<?php

/**
 * sfSympalComment form base class.
 *
 * @method sfSympalComment getObject() Returns the current form's model object
 *
 * @package    sympal
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasesfSympalCommentForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'            => new sfWidgetFormInputHidden(),
      'status'        => new sfWidgetFormChoice(array('choices' => array('Pending' => 'Pending', 'Approved' => 'Approved', 'Denied' => 'Denied'))),
      'user_id'       => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Author'), 'add_empty' => true)),
      'name'          => new sfWidgetFormInputText(),
      'email_address' => new sfWidgetFormInputText(),
      'website'       => new sfWidgetFormInputText(),
      'body'          => new sfWidgetFormTextarea(),
      'created_at'    => new sfWidgetFormDateTime(),
      'updated_at'    => new sfWidgetFormDateTime(),
      'content_list'  => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalContent')),
    ));

    $this->setValidators(array(
      'id'            => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'status'        => new sfValidatorChoice(array('choices' => array(0 => 'Pending', 1 => 'Approved', 2 => 'Denied'), 'required' => false)),
      'user_id'       => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Author'), 'required' => false)),
      'name'          => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'email_address' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'website'       => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'body'          => new sfValidatorString(),
      'created_at'    => new sfValidatorDateTime(),
      'updated_at'    => new sfValidatorDateTime(),
      'content_list'  => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalContent', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sf_sympal_comment[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'sfSympalComment';
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
    $this->saveContentList($con);

    parent::doSave($con);
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

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->Content->getPrimaryKeys();
    $values = $this->getValue('content_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Content', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Content', array_values($link));
    }
  }

}
