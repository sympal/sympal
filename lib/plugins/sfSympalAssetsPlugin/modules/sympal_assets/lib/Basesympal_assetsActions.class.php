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

    $this->_synchronizeAssets($this->directory);

    $this->assets = Doctrine_Core::getTable('sfSympalAsset')->findByPath($this->directory);

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
    $this->asset = $this->getRoute()->getObject();
    $this->askConfirmation(
      'Are you sure?',
      sprintf('Are you sure you want to delete the asset named "%s"?', $this->asset->getName())
    );

    $dir = $this->asset->getRelativePathDirectory();
    $this->asset->delete();

    $this->getUser()->setFlash('notice', 'File successfully deleted.');
    $this->redirect('@sympal_assets?dir='.$dir);
  }

  public function executeEdit_asset(sfWebRequest $request)
  {
    $this->asset = $this->getRoute()->getObject();

    if ($request->isMethod('post'))
    {
      $this->form = new sfSympalAssetEditForm();
      $this->form->bind($request->getParameter($this->form->getName()));

      if ($this->form->isValid())
      {
        $this->asset->move(dirname($this->asset->getPath()).'/'.$this->form->getValue('new_name'));
        $this->asset->save();

        $this->redirect($this->generateUrl('sympal_assets_edit_asset', $this->asset));
      }
    }
    else
    {
      $this->form = new sfSympalAssetEditForm(array(
        'new_name' => $this->asset->getName(),
        'current_name' => $this->asset->getName(),
        'directory' => $this->asset->getRelativePathDirectory()
      ));
    }
  }

  public function executeSelect(sfWebRequest $request)
  {
    foreach ($this->getResponse()->getStylesheets() as $key => $value)
    {
      $this->getResponse()->removeStylesheet($key);
    }
    $this->setLayout(false);
    $this->executeIndex($request);
  }

  private function _getDirectories($path)
  {
    return sfFinder::type('dir')
      ->maxdepth(0)
      ->prune('.*')
      ->discard('.*')
      ->relative()
      ->in($path);
  }

  private function _synchronizeAssets($path)
  {
    $synchronizer = new sfSympalAssetSynchronizer($this->getContext()->getEventDispatcher());
    $synchronizer->run();
  }

  private function _getParentDir($path)
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
}