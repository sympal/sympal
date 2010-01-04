<?php

class sfSympalAssetEditForm extends sfForm
{
  private $_asset;

  public function setAsset(sfSympalAsset $asset)
  {
    $this->_asset = $asset;

    if ($this->_asset->isImage())
    {
      $this->widgetSchema['width'] = new sfWidgetFormInput();
      $this->widgetSchema['height'] = new sfWidgetFormInput();
    }

    if ($this->_asset->isImage())
    {
      $this->validatorSchema['width'] = new sfValidatorString(array('trim' => true));
      $this->validatorSchema['height'] = new sfValidatorString(array('trim' => true));
    }
  }

  public function configure()
  {
    $this->setWidgets(array(
      'new_name'     => new sfWidgetFormInput(),
      'current_name' => new sfWidgetFormInputHidden(),
      'directory'    => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'new_name'      => new sfValidatorString(array('trim' => true)),
      'current_name'  => new sfValidatorString(),
      'directory'     => new sfValidatorString(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('rename[%s]');
  }
}