<?php

class sfSympalAssetEditForm extends sfForm
{
  private $_asset;
  private $_uploadDirectory;

  public function configure()
  {
    $this->setWidgets(array(
      'file'         => new sfWidgetFormInputFile(),
      'new_name'     => new sfWidgetFormInput(),
      'current_name' => new sfWidgetFormInputHidden(),
      'directory'    => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'file'          => new sfValidatorFile(array('path' => $this->getUploadDirectory())),
      'new_name'      => new sfValidatorString(array('trim' => true)),
      'current_name'  => new sfValidatorString(),
      'directory'     => new sfValidatorString(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('rename[%s]');
  }

  public function setAsset(sfSympalAsset $asset)
  {
    $this->setUploadDirectory($asset->getRootPath());

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

  public function getUploadDirectory()
  {
    if(!$this->_uploadDirectory)
    {
      $this->_uploadDirectory = sfConfig::get('sf_upload_dir');
    }
    return $this->_uploadDirectory;
  }

  public function setUploadDirectory($directory)
  {
    $this->_uploadDirectory = $directory;
  }
}