<?php

/**
 * MenuItemGroup form base class.
 *
 * @package    form
 * @subpackage menu_item_group
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseMenuItemGroupForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'menu_item_id' => new sfWidgetFormInputHidden(),
      'group_id'     => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'menu_item_id' => new sfValidatorDoctrineChoice(array('model' => 'MenuItemGroup', 'column' => 'menu_item_id', 'required' => false)),
      'group_id'     => new sfValidatorDoctrineChoice(array('model' => 'MenuItemGroup', 'column' => 'group_id', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('menu_item_group[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'MenuItemGroup';
  }

}