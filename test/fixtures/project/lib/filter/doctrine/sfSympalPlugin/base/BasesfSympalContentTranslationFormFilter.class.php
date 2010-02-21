<?php

/**
 * sfSympalContentTranslation filter form base class.
 *
 * @package    elf-ekb.ru
 * @subpackage filter
 * @author     Cluster Studio <clusterstudio@gmail.com>
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasesfSympalContentTranslationFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'page_title'       => new sfWidgetFormFilterInput(),
      'meta_keywords'    => new sfWidgetFormFilterInput(),
      'meta_description' => new sfWidgetFormFilterInput(),
      'i18n_slug'        => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'page_title'       => new sfValidatorPass(array('required' => false)),
      'meta_keywords'    => new sfValidatorPass(array('required' => false)),
      'meta_description' => new sfValidatorPass(array('required' => false)),
      'i18n_slug'        => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sf_sympal_content_translation_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'sfSympalContentTranslation';
  }

  public function getFields()
  {
    return array(
      'id'               => 'Number',
      'page_title'       => 'Text',
      'meta_keywords'    => 'Text',
      'meta_description' => 'Text',
      'i18n_slug'        => 'Text',
      'lang'             => 'Text',
    );
  }
}
