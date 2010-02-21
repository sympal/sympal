<?php

/**
 * sfSympalPlugin form base class.
 *
 * @method sfSympalPlugin getObject() Returns the current form's model object
 *
 * @package    sympal
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasesfSympalPluginForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'plugin_author_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Author'), 'add_empty' => true)),
      'title'            => new sfWidgetFormInputText(),
      'name'             => new sfWidgetFormInputText(),
      'description'      => new sfWidgetFormTextarea(),
      'summary'          => new sfWidgetFormTextarea(),
      'image'            => new sfWidgetFormInputText(),
      'users'            => new sfWidgetFormInputText(),
      'scm'              => new sfWidgetFormInputText(),
      'homepage'         => new sfWidgetFormInputText(),
      'ticketing'        => new sfWidgetFormInputText(),
      'link'             => new sfWidgetFormInputText(),
      'is_downloaded'    => new sfWidgetFormInputCheckbox(),
      'is_installed'     => new sfWidgetFormInputCheckbox(),
      'is_theme'         => new sfWidgetFormInputCheckbox(),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'plugin_author_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Author'), 'required' => false)),
      'title'            => new sfValidatorString(array('max_length' => 255)),
      'name'             => new sfValidatorString(array('max_length' => 255)),
      'description'      => new sfValidatorString(array('required' => false)),
      'summary'          => new sfValidatorString(array('required' => false)),
      'image'            => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'users'            => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'scm'              => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'homepage'         => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'ticketing'        => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'link'             => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'is_downloaded'    => new sfValidatorBoolean(array('required' => false)),
      'is_installed'     => new sfValidatorBoolean(array('required' => false)),
      'is_theme'         => new sfValidatorBoolean(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sf_sympal_plugin[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'sfSympalPlugin';
  }

}
