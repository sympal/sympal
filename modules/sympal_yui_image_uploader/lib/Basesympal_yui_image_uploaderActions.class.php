<?php

/**
 * Base actions for the sfSympalPlugin sympal_yui_image_uploader module.
 * 
 * @package     sfSympalPlugin
 * @subpackage  sympal_yui_image_uploader
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class Basesympal_yui_image_uploaderActions extends sfActions
{
  public function executeUpload(sfWebRequest $request)
  {
    $dirName = sfSympalConfig::get('yui_image_upload_dir', null, 'yui_images');
    $uploadDir = sfConfig::get('sf_upload_dir').'/'.$dirName;
    if (!is_dir($uploadDir))
    {
      mkdir($uploadDir, 0777, true);
    }
    $file = $request->getFiles('image');
    $fileName = $this->sanitizeFileName($file['name']);
    $filePath = $uploadDir.'/'.$fileName;
    move_uploaded_file($file['tmp_name'], $filePath);
    $url = $request->getRelativeUrlRoot().'/uploads/'.$dirName.'/'.$fileName;
    exit("{status:'UPLOADED', image_url:'".$url."'}");
  }
  
  protected function sanitizeFileName($fileName)
  {
    return time().'-'.preg_replace('/[^a-z0-9_\.-]/i', '_', $fileName);
  }
}