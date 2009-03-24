<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/doctrine/BaseFormFilterDoctrine.class.php');

/**
 * ContentSlotTranslation filter form base class.
 *
 * @package    filters
 * @subpackage ContentSlotTranslation *
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class BaseContentSlotTranslationFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'value' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'value' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('content_slot_translation_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'ContentSlotTranslation';
  }

  public function getFields()
  {
    return array(
      'id'    => 'Number',
      'value' => 'Text',
      'lang'  => 'Text',
    );
  }
}