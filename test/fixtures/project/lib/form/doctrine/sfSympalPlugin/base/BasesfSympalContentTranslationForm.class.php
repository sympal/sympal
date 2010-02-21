<?php

/**
 * sfSympalContentTranslation form base class.
 *
 * @method sfSympalContentTranslation getObject() Returns the current form's model object
 *
 * @package    elf-ekb.ru
 * @subpackage form
 * @author     Cluster Studio <clusterstudio@gmail.com>
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasesfSympalContentTranslationForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'page_title'       => new sfWidgetFormInputText(),
      'meta_keywords'    => new sfWidgetFormTextarea(),
      'meta_description' => new sfWidgetFormTextarea(),
      'i18n_slug'        => new sfWidgetFormInputText(),
      'lang'             => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'page_title'       => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'meta_keywords'    => new sfValidatorString(array('max_length' => 500, 'required' => false)),
      'meta_description' => new sfValidatorString(array('max_length' => 500, 'required' => false)),
      'i18n_slug'        => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'lang'             => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'lang', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sf_sympal_content_translation[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'sfSympalContentTranslation';
  }

}
