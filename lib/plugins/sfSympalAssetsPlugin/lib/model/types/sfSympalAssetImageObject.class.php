<?php

/**
 * Asset object representing an image
 *
 * @package     sfSympalAssetsPlugin
 * @subpackage  type
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-03-06
 * @version     svn:$Id$ $Author$
 */
class sfSympalAssetImageObject extends sfSympalAssetFileObject
{
  /**
   * @var array $_dimensions Size array, index 0 is width, index 1 is height
   * @var array $_thumbnails An array of thumbnail objects related to this image object
   */
  protected
    $_dimensions = null,
    $_thumbnails = array();

  protected
    $_type = 'image';

  /**
   * @see getimagesize()
   */
  public function getDimensions()
  {
    if (!$this->_dimensions)
    {
      $this->_dimensions = getimagesize($this->getPath());
    }
    return $this->_dimensions;
  }


  public function getWidth()
  {
    $dimensions = $this->getDimensions();
    return $dimensions[0];
  }

  public function getHeight()
  {
    $dimensions = $this->getDimensions();
    return $dimensions[1];
  }

  /**
   * Resizes this image
   *
   * This will replace this image file with the resized version
   */
  public function resize($width, $height, $method = null)
  {
    $thumb = new sfImage($this->getOriginal()->getPath());
    $thumb->thumbnail($width, $height, $method);

    return $thumb->saveAs($this->getPath());
  }

  /**
   * Crops this image
   *
   * This replaces the actual image file with the cropped version
   * @return boolean The success of the cropping
   */
  public function cropImage($x, $y, $w, $h)
  {
    $targetWidth = $w;
    $targetHeight = $h;
  	$quality = 90;

    $extension = $this->getExtension();
    $type = $extension == 'jpg' ? 'jpeg' : $extension;
    $func = 'imagecreatefrom'.$type;
  	$imgR = $func($this->getOriginal()->getPath());
  	$destR = imagecreatetruecolor($targetWidth, $targetHeight);
  	imagecopyresampled($destR ,$imgR, 0, 0, $x, $y, $targetWidth, $targetHeight, $w, $h);

    $func = 'image'.$type;

  	return $func($destR, $this->getPath(), $quality);
  }

  /**
   * Retrieves an sfSympalAssetImageObject representing a thumbnail version
   * of this image
   *
   * @param integer $width The width for the thumbnail
   * @param integer $height The height for the thumbnail
   * @param string $method The method for thumbnailing (fit, scale, inflate, deflate, left, right, top, bottom, center)
   *
   * @return sfSympalAssetImageObject
   */
  public function getThumbnail($width = null, $height = null, $method = null)
  {
    $width = ($width === null) ? sfSympalConfig::get('assets', 'thumbnails_max_width', 64) : $width;
    $height = ($height === null) ? sfSympalConfig::get('assets', 'thumbnails_max_height', 64) : $height;
    $method = ($method === null) ? sfSympalConfig::get('assets', 'thumbnails_method', 'fit') : $method;

    $thumbnailKey = $width.'_'.$height.'_'.$method;

    if (!isset($this->_thumbnails[$thumbnailKey]))
    {
      $thumbnailPath = $this->getThumbnailDirectory($width, $height, $method).'/'.$this->getName();
      if (!file_exists($thumbnailPath))
      {
        // the thumbnail file doesn't exist, create it
        $this->_generateThumbnail($width, $height, $method);
      }

      $thumbnail = new self(
        $this->getRelativePathDirectory()
        .'/'.sfSympalConfig::get('assets', 'thumbnails_dir')
        .'/'.$this->getThumbnailSubdirectory($width, $height, $method)
        .'/'.$this->getName()
      );

      if ($thumbnail->exists())
      {
        $this->_thumbnails[$thumbnailKey] = $thumbnail;
      }
      else
      {
        $this->_thumbnails[$thumbnailKey] = false;
      }
    }

    return $this->_thumbnails[$thumbnailKey];
  }

  /**
   * Returns the url to a thumbnail of this image with the given width,
   * height, and thumbnailing method
   *
   * @see sfSympalAssetImageObject::getThumbnail()
   * @return string
   */
  public function getThumbnailUrl($width = null, $height = null, $method = null)
  {
    if ($thumbnail = $this->getThumbnail($width, $height, $method))
    {
      return $thumbnail->getUrl();
    }
    else
    {
      return null;
    }
  }

  /**
   * In addition to moving this image, all the thumbnails must all be moved
   *
   * @see sfSympalAssetObject::move()
   */
  public function move($newPath)
  {
    $originalPathDirectory = $this->getPathDirectory();
    $thumbnails = $this->getAllThumbnails();

    parent::move($newPath);

    foreach ($thumbnails as $thumbnail)
    {
      $newPath = str_replace($originalPathDirectory, dirname($newPath), $thumbnail->getPath());
      $thumbnail->move($newPath);
    }
  }

  /**
   * Also takes care of deleting the associated thumbnails of this image
   *
   * @see sfSympalAssetObject::delete()
   */
  public function delete()
  {
    foreach ($this->getAllThumbnails() as $thumbnail)
    {
      $thumbnail->delete();
    }

    parent::delete();
  }

  public function getEmbed($options = array())
  {
    return image_tag($this->getUrl(), $options);
  }

  /**
   * Returns the absolute dir path for a thumbnail based on the width,
   * height and thumbnailing method
   *
   * @return string
   */
  public function getThumbnailDirectory($width, $height, $method)
  {
    $rootThumbnailDir = $this->getPathDirectory().'/'.sfSympalConfig::get('assets', 'thumbnails_dir', '.thumbnails');

    return $rootThumbnailDir.'/'.$this->getThumbnailSubdirectory($width, $height, $method);
  }

  /**
   * Returns the subdirectory path inside the thumbnails directory for
   * a thumbnail based on its width, height and method
   *
   * @return string (e.g. 200/200/fit)
   */
  protected function getThumbnailSubdirectory($width, $height, $method)
  {
    return $width.'/'.$height.'/'.$method;
  }

  /**
   * Generates and saves a thumbnail version of this image
   *
   * @param array $options An array of thumbnailing options.
   * @return boolean The success/failure of the operation
   */
  private function _generateThumbnail($width, $height, $method)
  {
    if (!sfSympalConfig::get('assets', 'thumbnails_enabled', false))
    {
      return;
    }

    if (!class_exists('sfImage'))
    {
      throw new sfException('sfImageTransformPlugin must be installed in order to generate thumbnails.');
    }

    if (file_exists($this->getPath()))
    {
      $thumb = new sfImage($this->getPath());
      $thumb->thumbnail(
        $width,
        $height,
        $method
      );

      $destinationDirectory = $this->getThumbnailDirectory($width, $height, $method);
      if(!file_exists($destinationDirectory))
      {
        // recursively create the directory
        mkdir($destinationDirectory, 0777, true);
        chmod($destinationDirectory, 0777);
      }

      return $thumb->saveAs($destinationDirectory.'/'.$this->getName());
    }
    else
    {
      return false;
    }
  }

  /**
   * Called when the related sfSympalAsset object is inserted or updated.
   *
   * This ensures that we have the "default" thumbnail already generated
   */
  public function save()
  {
    $this->getThumbnail();
  }

  /**
   * Returns an array of sfSympalAssetImageObjects representing all the
   * thumbnails that relate back to this image.
   *
   * This is could be a filesystem-intensive, so it should only be used
   * when needing to perform some operation or on the backend
   *
   * @return array The array of sfSympalImageObject instances
   */
  public function getAllThumbnails()
  {
    $thumbnailDir = sfSympalConfig::get('assets', 'thumbnails_dir', '.thumbnails');

    $thumbnails = sfFinder::type('file')
      ->name($this->getName())
      ->relative()
      ->in($this->getPathDirectory().'/'.$thumbnailDir);

    $thumbnailObjects = array();
    foreach ($thumbnails as $thumbnail)
    {
      $thumbnailObjects[] = new self($this->getRelativePathDirectory().'/'.$thumbnailDir.'/'.$thumbnail);
    }

    return $thumbnailObjects;
  }
}