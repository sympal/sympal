<?php

/**
 * Responsible for creating sfSympalAsset records for any uploaded asset
 *
 * @package     sfSympalAssetPlugin
 * @subpackage  util
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @since       2010-03-06
 * @version     svn:$Id$ $Author$
 */
class sfSympalAssetSynchronizer
{
  private $_dispatcher;
  private $_path;

  public function __construct($dispatcher)
  {
    $this->_dispatcher = $dispatcher;
    $this->_path = sfConfig::get('sf_web_dir').sfSympalConfig::get('assets', 'root_dir', '/uploads');
  }

  public function run()
  {
    $this->synchronizeDirectory($this->_path);

    $dirs = sfFinder::type('dir')
      ->prune('.*')
      ->discard('.*')
      ->relative()
      ->in($this->_path);
    foreach ($dirs as $dir)
    {
      $this->synchronizeDirectory($this->_path.'/'.$dir);
    }
  }

  public function synchronizeDirectory($dir)
  {
    $files = sfFinder::type('file')
      ->maxdepth(0)
      ->relative()
      ->in($dir);

    $path = str_replace($this->_path, null, $dir);

    $exists = Doctrine_Core::getTable('sfSympalAsset')->findByPath($path);
    $keys = array();
    foreach ($exists as $asset)
    {
      $keys[$asset->getName()] = $asset;
    }

    foreach ($files as $file)
    {
      if (!isset($keys[$file]))
      {
        $assetObject = sfSympalAssetToolkit::createAssetObject($path.'/'.$file);
        $doctrineAsset = new sfSympalAsset();
        $doctrineAsset->setAssetObject($assetObject);
        $doctrineAsset->save();
      }
    }

    // Remove assets that don't exist anymore on disk
    foreach ($keys as $name => $asset)
    {
      if (!$asset->fileExists())
      {
        $asset->delete();
      }
    }
  }
}