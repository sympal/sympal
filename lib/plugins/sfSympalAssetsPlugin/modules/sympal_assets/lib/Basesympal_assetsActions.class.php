<?php

class Basesympal_assetsActions extends sfActions
{
  public function preExecute()
  {
    $this->loadAdminTheme();

    $this->isAjax = $this->isAjax();
    if ($this->isAjax)
    {
      $this->setLayout(false);
    }

    $request = $this->getRequest();

    $this->rootPath = sfConfig::get('sf_web_dir').sfSympalConfig::get('assets', 'root_dir', '/uploads');
    $dir = $request->getParameter('dir') == '/' ? null : $request->getParameter('dir');
    $this->directory = urldecode($dir);
    $this->parentDirectory = $this->_getParentDir($this->directory);
    $this->fullPath = $this->rootPath.'/'.$this->directory;

    $this->currentRoute = $this->getContext()->getRouting()->getCurrentRouteName();
    $this->currentParams = $request->getGetParameters();
  }

  public function executeIndex(sfWebRequest $request)
  {
    $this->directories = $this->_getDirectories($this->fullPath);

    $this->_synchronizeAssets($this->directory);

    $this->assets = Doctrine_Core::getTable('sfSympalAsset')->findByPath($this->directory);

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

    $dir = str_replace(sfConfig::get('sf_web_dir').sfSympalConfig::get('assets', 'root_dir'), null, dirname($path));
    if ($this->isAjax)
    {
      $this->redirect('@sympal_assets_select?is_ajax=&dir='.$dir);
    } else {
      $this->redirect('@sympal_assets?dir='.$dir);
    }
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
    if ($this->isAjax)
    {
      $this->redirect('@sympal_assets_select?is_ajax='.$this->isAjax.'&dir='.$upload['directory']);
    } else {
      $this->redirect('@sympal_assets?is_ajax='.$this->isAjax.'&dir='.$upload['directory']);      
    }
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

    if ($this->isAjax)
    {
      $this->redirect('@sympal_assets_select?is_ajax=&dir='.$dir);
    } else {
      $this->getUser()->setFlash('notice', 'File successfully deleted.');
      $this->redirect('@sympal_assets?is_ajax=&dir='.$dir);
    }
  }

  public function executeEdit_asset(sfWebRequest $request)
  {
    $this->asset = $this->getRoute()->getObject();

    if ($request->isMethod('post'))
    {
      $this->form = new sfSympalAssetEditForm();
      $this->form->setAsset($this->asset);
      $this->form->bind($request->getParameter($this->form->getName()), $request->getFiles($this->form->getName()));

      if ($this->form->isValid())
      {
        $postFile = $this->form->getValue('file');
        if ($postFile)
        {
          $fileName = $postFile->getOriginalName();
          $name = Doctrine_Inflector::urlize(sfSympalAssetToolkit::getNameFromFile($fileName));
          $extension = pathinfo($fileName, PATHINFO_EXTENSION);
          $fullName = $extension ? $name.'.'.$extension : $name;
          $newPath = $this->asset->getPathDirectory().'/'.$fullName;
          $this->asset->move($newPath);
          $postFile->save($newPath);
          $this->asset->save();
          $this->asset->copyOriginal();
        } else {
          if ($this->asset->isImage())
          {
            $this->asset->resize($this->form->getValue('width'), $this->form->getValue('height'));
          }

          $this->asset->rename($this->form->getValue('new_name'));
        }

        $this->asset->save();

        if ($this->isAjax)
        {
          $this->redirect('@sympal_assets_select?is_ajax=1&dir='.$this->asset->getRelativePathDirectory());
        } else {
          $this->redirect($this->generateUrl('sympal_assets_edit_asset', $this->asset));
        }
      }
    }
    else
    {
      $values = array(
        'new_name' => $this->asset->getName(),
        'current_name' => $this->asset->getName(),
        'directory' => $this->asset->getRelativePathDirectory()
      );
      if ($this->asset->isImage())
      {
        $values['width'] = $this->asset->getWidth();
        $values['height'] = $this->asset->getHeight();
      }
      $this->form = new sfSympalAssetEditForm($values);
      $this->form->setAsset($this->asset);
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

  public function executeSave_image_crop(sfWebRequest $request)
  {
    $asset = $this->getRoute()->getObject();

    $x = $request->getParameter('x');
    $y = $request->getParameter('y');
    $w = $request->getParameter('w');
    $h = $request->getParameter('h');

    $asset->cropImage($x, $y, $w, $h);
    $asset->save();

    if ($this->isAjax)
    {
      $this->redirect('@sympal_assets_select?is_ajax=1&dir='.$asset->getRelativePathDirectory());
    } else {
      $this->redirect($this->generateUrl('sympal_assets_edit_asset', $asset));
    }
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