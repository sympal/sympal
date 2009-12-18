<?php

class sfSympalYuiImageUploadForm extends sfForm
{
  public $uploadDir;

  public function setup()
  {
    $dirName = sfSympalConfig::get('yui_image_upload_dir', null, 'yui_images');
    $this->uploadDir = sfConfig::get('sf_upload_dir').'/'.$dirName;
    if (!is_dir($this->uploadDir))
    {
      mkdir($this->uploadDir, 0777, true);
    }

    $this->setWidgets(array(
      'image'        => new sfWidgetFormInputFile(),
    ));

    $this->setValidators(array(
      'image'        => new sfValidatorFile(array('path' => $this->uploadDir)),
    ));

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function save()
  {
    $file = $this->getValue('image');
    $file->save($this->uploadDir.'/'.time().'-'.$file->getOriginalName());

    return $file->getSavedName();
  }
}