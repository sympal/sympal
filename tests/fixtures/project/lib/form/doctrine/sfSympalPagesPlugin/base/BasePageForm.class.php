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
      'content_id'       => new sfWidgetFormDoctrineChoice(array('model' => 'Content', 'add_empty' => true)),
      'title'            => new sfWidgetFormInput(),
      'disable_comments' => new sfWidgetFormInputCheckbox(),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorDoctrineChoice(array('model' => 'Page', 'column' => 'id', 'required' => false)),
      'content_id'       => new sfValidatorDoctrineChoice(array('model' => 'Content', 'required' => false)),
      'title'            => new sfValidatorString(array('max_length' => 255)),
      'disable_comments' => new sfValidatorBoolean(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('page[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Page';
  }

}