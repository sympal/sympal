<?php

class sfSympalAssetsImageObject extends sfSympalAssetsFileObject
{
  protected
    $_dimensions = null,
    $_thumbnail = null;

  public function __construct($file)
  {
    parent::__construct($file);

    if ($this->getType() != 'image')
    {
      throw new sfException(sprintf('The file "%s" is not an image', $file));
    }
  }

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
}