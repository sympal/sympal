<?php

/**
 * sfSympalContentType filter form base class.
 *
 * @package    sympal
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasesfSympalContentTypeFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'description'  => new sfWidgetFormFilterInput(),
      'label'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'plugin_name'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'default_path' => new sfWidgetFormFilterInput(),
      'theme'        => new sfWidgetFormFilterInput(),
      'template'     => new sfWidgetFormFilterInput(),
      'module'       => new sfWidgetFormFilterInput(),
      'action'       => new sfWidgetFormFilterInput(),
      'slug'         => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'name'         => new sfValidatorPass(array('required' => false)),
      'description'  => new sfValidatorPass(array('required' => false)),
      'label'        => new sfValidatorPass(array('required' => false)),
      'plugin_name'  => new sfValidatorPass(array('required' => false)),
      'default_path' => new sfValidatorPass(array('required' => false)),
      'theme'        => new sfValidatorPass(array('required' => false)),
      'template'     => new sfValidatorPass(array('required' => false)),
      'module'       => new sfValidatorPass(array('required' => false)),
      'action'       => new sfValidatorPass(array('required' => false)),
      'slug'         => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sf_sympal_content_type_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'sfSympalContentType';
  }

  public function getFields()
  {
    return array(
      'id'           => 'Number',
      'name'         => 'Text',
      'description'  => 'Text',
      'label'        => 'Text',
      'plugin_name'  => 'Text',
      'default_path' => 'Text',
      'theme'        => 'Text',
      'template'     => 'Text',
      'module'       => 'Text',
      'action'       => 'Text',
      'slug'         => 'Text',
    );
  }
}
