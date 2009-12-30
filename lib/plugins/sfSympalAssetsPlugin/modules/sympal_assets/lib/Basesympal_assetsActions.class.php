<?php

class Basesympal_assetsActions extends sfActions
{
  public function preExecute()
  {
    $this->useAdminLayout();

    $request = $this->getRequest();

    $this->rootPath = sfConfig::get('sf_web_dir').sfSympalConfig::get('assets', 'root_dir', '/uploads');
    $dir = $request->getParameter('dir') == '/' ? null : $request->getParameter('dir');
    $this->directory = urldecode($dir);
    $this->parentDirectory = $this->_getParentDir($this->directory);
    $this->fullPath = $this->rootPath.'/'.$this->directory;
  }

  public function executeIndex(sfWebRequest $request)
  {
    $this->directories = $this->_getDirectories($this->fullPath);

    $this->assets = $this->_getAssets($this->directory);

    $this->currentRoute = $this->getContext()->getRouting()->getCurrentRouteName();
    $this->currentParams = $request->getGetParameters();

    $this->uploadForm = new sfSympalAssetUploadForm(array('directory' => $this->directory));
    $this->directoryForm = new sfSympalAssetsDirectoryForm(array('directory' => $this->directory));
  }

  public function executeCreate_directory(sfWebRequest $request)
  {
    $form = new sfSympalAssetsDirectoryForm();
    $form->bind($request->getParameter($form->getName()));

    if ($form->isValid())
    {
      $realPath = realpath(sfConfig::get('sf_web_dir').'/'.sfSympalConfig::get('assets', 'root_dir').'/'.$form->getValue('directory'));
      $fullPath = $realPath.'/'.$form->getValue('name');
      if (mkdir($fullPath))
      {
        chmod($fullPath, 0777);
        $this->getUser()->setFlash('notice', 'Directory created successfully.');
      }
      else
      {
        $this->getUser()->setFlash('error', 'Could not create directory.');
      }
    }
    $this->redirect($request->getReferer());
  }

  public function executeDelete_directory(sfWebRequest $request)
  {
    $path = sfConfig::get('sf_web_dir').sfSympalConfig::get('assets', 'root_dir').urldecode($request->getParameter('directory'));

    $this->askConfirmation(
      'Are you sure?',
      'Are you sure you want to delete this directory?'
    );

    if (sfSympalAssetToolkit::deleteRecursive(urldecode($path)))
    {
      $this->getUser()->setFlash('notice', 'Directory deleted successfully.');
    }
    else
    {
      $this->getUser()->setFlash('error', 'Could not delete directory.');
    }
    $this->redirect('@sympal_assets?dir='.str_replace(sfConfig::get('sf_web_dir').sfSympalConfig::get('assets', 'root_dir'), null, dirname($path)));
  }

  public function executeCreate_asset(sfWebRequest $request)
  {
    $form = new sfSympalAssetUploadForm();

    $upload = $request->getParameter($form->getName());
    $form->setUploadDirectory($upload['directory']);
    $form->bind($upload, $request->getFiles($form->getName()));

    if ($form->isValid())
    {
      $postFile = $form->getValue('file');
      $fileName = $postFile->getOriginalName();
      $name = Doctrine_Inflector::urlize(sfSympalAssetToolkit::getNameFromFile($fileName));
      $extension = pathinfo($fileName, PATHINFO_EXTENSION);
      $fullName = $extension ? $name.'.'.$extension : $name;
      $destinationDirectory = sfConfig::get('sf_web_dir').'/'.sfSympalConfig::get('assets', 'root_dir').$upload['directory'];

      if (sfSympalConfig::get('assets', 'thumbnails_enabled', false) && sfSympalAssetToolkit::getTypeFromExtension($extension) == 'image')
      {
        $this->generateThumbnail($postFile->getTempName(), $fullName, $destinationDirectory);
      }

      $this->getUser()->setFlash('notice', 'File uploaded successfully.');
      $postFile->save($destinationDirectory.'/'.$fullName);

      $assetObject = sfSympalAssetToolkit::createAssetObject($upload['directory'].'/'.$fullName);
      if (!$asset = $assetObject->getDoctrineAsset())
      {
        $asset = new sfSympalAsset();
      }
      $asset->path = $assetObject->getRelativePathDirectory();
      $asset->name = $assetObject->getName();
      $asset->save();
    }
    else
    {
      $this->getUser()->setFlash('error', 'Could not upload file.');
    }
    $this->redirect($request->getReferer());
  }

  public function executeDelete_asset(sfWebRequest $request)
  {
    $asset = sfSympalAssetToolkit::createAssetObject(urldecode($request->getParameter('file')));

    $this->askConfirmation(
      'Are you sure?',
      sprintf('Are you sure you want to delete the asset named "%s"?<h3>'.$asset->getRelativePath().'</h3>', $asset->getName())
    );

    $dir = $asset->getRelativePathDirectory();
    $name = $asset->getName();

    $asset->delete();

    Doctrine_Core::getTable('sfSympalAsset')
      ->createQuery('a')
      ->delete()
      ->where('path = ?', $dir)
      ->andWhere('name = ?', $name)
      ->execute();

    $this->getUser()->setFlash('notice', 'File successfully deleted.');
    $this->redirect('@sympal_assets?dir='.$dir);
  }

  public function executeEdit_asset(sfWebRequest $request)
  {
    if ($request->isMethod('post'))
    {
      $this->form = new sfSympalAssetEditForm();
      $this->form->bind($request->getParameter($this->form->getName()));

      $this->asset = sfSympalAssetToolkit::createAssetObject($this->form->getValue('directory').'/'.$this->form->getValue('current_name'));

      if ($this->form->isValid())
      {
        $doctrineAsset = $this->asset->getDoctrineAsset();
        $doctrineAsset->move(dirname($this->asset->getPath()).'/'.$this->form->getValue('new_name'));
        $doctrineAsset->save();

        $this->redirect($this->generateUrl('sympal_assets_edit_asset', array('file' => dirname($this->asset->getFilePath()).'/'.$this->form->getValue('new_name'))));
      }
    }
    else
    {
      $this->asset = sfSympalAssetToolkit::createAssetObject(urldecode($request->getParameter('file')));
      $this->form = new sfSympalAssetEditForm(array(
        'new_name' => $this->asset->getName(),
        'current_name' => $this->asset->getName(),
        'directory' => $this->asset->getRelativePathDirectory()
      ));
    }
  }

  protected function _getDirectories($path)
  {
    return sfFinder::type('dir')
      ->maxdepth(0)
      ->prune('.*')
      ->discard('.*')
      ->relative()
      ->in($path);
  }

  protected function _getAssets($path)
  {
    $files = sfFinder::type('file')
      ->maxdepth(0)
      ->relative()
      ->in($this->rootPath.'/'.$path);

    $assets = Doctrine_Core::getTable('sfSympalAsset')
      ->createQuery('a')
      ->from('sfSympalAsset a INDEXBY a.name')
      ->andWhere('a.path = ?', $path)
      ->execute();

    foreach ($files as $file)
    {
      if (!isset($assets[$file]))
      {
        $assetObject = sfSympalAssetToolkit::createAssetObject($path.'/'.$file);
        $doctrineAsset = new sfSympalAsset();
        $doctrineAsset->setAssetObject($assetObject);
        $doctrineAsset->save();

        $assets->add($doctrineAsset);
      }
    }

    return $assets;
  }

  protected function _getParentDir($path)
  {
    // Remove trailing slash
    if(substr($path, -1, 1) == '/')
    {
      $path = substr($path, 0, -1);
    }
    // Find last slash
    $slashPos = strrpos($path, '/');

    // return root if path is a root subfolder
    if($slashPos === 0)
    {
      return '/';
    }

    return (string) substr($path, 0, $slashPos);
  }

  protected function generateThumbnail($sourceFile, $destinationName, $destinationDirectory)
  {
    if(!class_exists('sfImage'))
    {
      throw new sfException('sfImageTransformPlugin must be installed in order to generate thumbnails.');
    }

    $thumb = new sfImage($sourceFile);
    $thumb->thumbnail(
      sfSympalConfig::get('assets', 'thumbnails_max_width', 64),
      sfSympalConfig::get('assets', 'thumbnails_max_height', 64)
    );

    $destinationDirectory = $destinationDirectory.'/'.sfSympalConfig::get('assets', 'thumbnails_dir', '.uploads');
    if(!file_exists($destinationDirectory))
    {
      mkdir($destinationDirectory);
      chmod($destinationDirectory, 0777);
    }
    return $thumb->saveAs($destinationDirectory.'/'.$destinationName);
  }
}