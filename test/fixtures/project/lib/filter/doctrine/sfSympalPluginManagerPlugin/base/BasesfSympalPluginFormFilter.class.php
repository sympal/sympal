<?php

/**
 * sfSympalPlugin filter form base class.
 *
 * @package    sympal
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasesfSympalPluginFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'plugin_author_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Author'), 'add_empty' => true)),
      'title'            => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'name'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'description'      => new sfWidgetFormFilterInput(),
      'summary'          => new sfWidgetFormFilterInput(),
      'image'            => new sfWidgetFormFilterInput(),
      'users'            => new sfWidgetFormFilterInput(),
      'scm'              => new sfWidgetFormFilterInput(),
      'homepage'         => new sfWidgetFormFilterInput(),
      'ticketing'        => new sfWidgetFormFilterInput(),
      'link'             => new sfWidgetFormFilterInput(),
      'is_downloaded'    => new sfWidgetFormChoice(array('choices' => array('' => 'yes or no', 1 => 'yes', 0 => 'no'))),
      'is_installed'     => new sfWidgetFormChoice(array('choices' => array('' => 'yes or no', 1 => 'yes', 0 => 'no'))),
      'is_theme'         => new sfWidgetFormChoice(array('choices' => array('' => 'yes or no', 1 => 'yes', 0 => 'no'))),
    ));

    $this->setValidators(array(
      'plugin_author_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Author'), 'column' => 'id')),
      'title'            => new sfValidatorPass(array('required' => false)),
      'name'             => new sfValidatorPass(array('required' => false)),
      'description'      => new sfValidatorPass(array('required' => false)),
      'summary'          => new sfValidatorPass(array('required' => false)),
      'image'            => new sfValidatorPass(array('required' => false)),
      'users'            => new sfValidatorPass(array('required' => false)),
      'scm'              => new sfValidatorPass(array('required' => false)),
      'homepage'         => new sfValidatorPass(array('required' => false)),
      'ticketing'        => new sfValidatorPass(array('required' => false)),
      'link'             => new sfValidatorPass(array('required' => false)),
      'is_downloaded'    => new sfValidatorChoice(array('required' => false, 'choices' => array('', 1, 0))),
      'is_installed'     => new sfValidatorChoice(array('required' => false, 'choices' => array('', 1, 0))),
      'is_theme'         => new sfValidatorChoice(array('required' => false, 'choices' => array('', 1, 0))),
    ));

    $this->widgetSchema->setNameFormat('sf_sympal_plugin_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'sfSympalPlugin';
  }

  public function getFields()
  {
    return array(
      'id'               => 'Number',
      'plugin_author_id' => 'ForeignKey',
      'title'            => 'Text',
      'name'             => 'Text',
      'description'      => 'Text',
      'summary'          => 'Text',
      'image'            => 'Text',
      'users'            => 'Text',
      'scm'              => 'Text',
      'homepage'         => 'Text',
      'ticketing'        => 'Text',
      'link'             => 'Text',
      'is_downloaded'    => 'Boolean',
      'is_installed'     => 'Boolean',
      'is_theme'         => 'Boolean',
    );
  }
}
