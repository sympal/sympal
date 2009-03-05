<?php

/**
 * MenuItemPermission form base class.
 *
 * @package    form
 * @subpackage menu_item_permission
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseMenuItemPermissionForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'menu_item_id'  => new sfWidgetFormInputHidden(),
      'permission_id' => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'menu_item_id'  => new sfValidatorDoctrineChoice(array('model' => 'MenuItemPermission', 'column' => 'menu_item_id', 'required' => false)),
      'permission_id' => new sfValidatorDoctrineChoice(array('model' => 'MenuItemPermission', 'column' => 'permission_id', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('menu_item_permission[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'MenuItemPermission';
  }

}