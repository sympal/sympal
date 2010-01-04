<?php

class sfSympalAssetUploadForm extends sfForm
{
  private $_uploadDirectory;

  public function configure()
  {
    $this->setWidgets(array(
      'file'      => new sfWidgetFormInputFile(),
      'directory' => new sfWidgetFormInputHidden(),
    ));

    $this->widgetSchema->setNameFormat('upload[%s]');

    $this->setValidators(array(
      'file'      => new sfValidatorFile(array('path' => $this->getUploadDirectory())),
      'directory' => new sfValidatorString(array('required' => false)),
    ));
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