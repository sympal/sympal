<?php

/**
 * sfSympalSiteTranslation filter form base class.
 *
 * @package    elf-ekb.ru
 * @subpackage filter
 * @author     Cluster Studio <clusterstudio@gmail.com>
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasesfSympalSiteTranslationFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'page_title'       => new sfWidgetFormFilterInput(),
      'meta_keywords'    => new sfWidgetFormFilterInput(),
      'meta_description' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'page_title'       => new sfValidatorPass(array('required' => false)),
      'meta_keywords'    => new sfValidatorPass(array('required' => false)),
      'meta_description' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sf_sympal_site_translation_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'sfSympalSiteTranslation';
  }

  public function getFields()
  {
    return array(
      'id'               => 'Number',
      'page_title'       => 'Text',
      'meta_keywords'    => 'Text',
      'meta_description' => 'Text',
      'lang'             => 'Text',
    );
  }
}
