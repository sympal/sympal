<?php

class sfSympalAssetImageObject extends sfSympalAssetFileObject
{
  protected
    $_dimensions = null,
    $_thumbnail = null,
    $_type = 'image';

  public function isImage()
  {
    return true;
  }

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

  public function resize($width, $height)
  {
    $thumb = new sfImage($this->getOriginal()->getPath());
    $thumb->thumbnail($width, $height);
    return $thumb->saveAs($this->getPath());
  }

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

  public function getThumbnail()
  {
    if (!$this->_thumbnail)
    {
      $thumbnail = new self($this->getRelativePathDirectory().'/'.sfSympalConfig::get('assets', 'thumbnails_dir').'/'.$this->getName());
      if ($thumbnail->exists())
      {
        $this->_thumbnail = $thumbnail;
      }
    }
    return $this->_thumbnail;
  }

  public function getThumbnailUrl()
  {
    return $this->getThumbnail()->getUrl();
  }

  public function move($newPath)
  {
    if ($thumbnail = $this->getThumbnail())
    {
      $newDir = $this->getRootPath().'/'.$this->getRelativePathDirectory().'/'.sfSympalConfig::get('assets', 'thumbnails_dir');
    }

    parent::move($newPath);

    if ($thumbnail)
    {
      $thumbnail->move($newDir.'/'.$this->getName());
    }
  }

  public function delete()
  {
    $thumbnail = $this->getThumbnail();
    if ($thumbnail && $thumbnail->exists())
    {
      $thumbnail->delete();
    }

    parent::delete();
  }

  public function getEmbed($options = array())
  {
    return image_tag($this->getUrl(), $options);
  }

  public function getThumbnailDirectory()
  {
    return $this->getPathDirectory().'/'.sfSympalConfig::get('assets', 'thumbnails_dir', '.uploads');
  }

  private function _generateThumbnail()
  {
    if (!class_exists('sfImage'))
    {
      throw new sfException('sfImageTransformPlugin must be installed in order to generate thumbnails.');
    }

    if (file_exists($this->getPath()))
    {
      $thumb = new sfImage($this->getPath());
      $thumb->thumbnail(
        sfSympalConfig::get('assets', 'thumbnails_max_width', 64),
        sfSympalConfig::get('assets', 'thumbnails_max_height', 64)
      );

      $destinationDirectory = $this->getThumbnailDirectory();
      if(!file_exists($destinationDirectory))
      {
        mkdir($destinationDirectory);
        chmod($destinationDirectory, 0777);
      }
      return $thumb->saveAs($destinationDirectory.'/'.$this->getName());
    } else {
      return false;
    }
  }

  public function save()
  {
    if (sfSympalConfig::get('assets', 'thumbnails_enabled', false))
    {
      $this->_generateThumbnail();
    }

    return parent::save();
  }
}