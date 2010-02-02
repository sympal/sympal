<?php

class sfSympalAssetObject
{
  protected
    $_filePath,
    $_rootPath,
    $_name,
    $_size,
    $_icon,
    $_doctrineAsset,
    $_original,
    $_type = 'file';

  public function __construct($filePath)
  {
    $this->_filePath = $filePath;    
    $this->_rootPath = sfConfig::get('sf_web_dir').sfSympalConfig::get('assets', 'root_dir', '/uploads');

    if ($this->getTypeFromExtension() != $this->_type && $this->_type !== 'file')
    {
      throw new sfException(sprintf('The file "%s" is not a %s', $file, $this->_type));
    }
  }

  public function exists()
  {
    return $this->getPath() && file_exists($this->getPath());
  }

  public function getTypeFromExtension()
  {
    return sfSympalAssetToolkit::getTypeFromExtension($this->getExtension());
  }

  public function getType()
  {
    return $this->_type;
  }

  public function isImage()
  {
    return false;
  }

  public function isFile()
  {
    return false;
  }

  public function getIcon()
  {
    if(!$this->_icon)
    {
      $this->_icon = sfSympalAssetToolkit::getIconFromExtension($this->getExtension());
    }
    return $this->_icon;
  }

  public function getExtension()
  {
    return pathinfo($this->getFilePath(), PATHINFO_EXTENSION);
  }

  public function getPath()
  {
    return realpath($this->_rootPath.'/'.$this->getFilePath());
  }

  public function getRelativePath()
  {
    return str_replace($this->getRootPath(), null, $this->getPath());
  }

  public function getRelativePathDirectory()
  {
    if ($this->getRootPath() == $this->getPathDirectory())
    {
      return '';
    } else {
      return str_replace($this->getRootPath(), null, $this->getPathDirectory());
    }
  }

  public function getFilePath()
  {
    return $this->_filePath;
  }

  public function getUrl()
  {
    $request = sfContext::getInstance()->getRequest();
    return sfSympalConfig::get('assets', 'web_path',  $request->getUriPrefix().$request->getRelativeUrlRoot()).sfSympalConfig::get('assets', 'root_dir', '/uploads').$this->getRelativePath();
  }
  
  public function getPathDirectory()
  {
    return dirname($this->getPath());
  }

  public function getRootPath()
  {
    return $this->_rootPath;
  }

  public function getName($withExtension = true)
  {
    $this->_name = pathinfo($this->_filePath, PATHINFO_FILENAME);
    $extension = $this->getExtension();
    return $withExtension && $extension ? $this->_name.'.'.$extension : $this->_name;
  }

  public function getSize($round = 1000)
  {
    if (!$this->_size)
    {
      $this->_size = filesize($this->getPath());
    }
    return $round >= 1 ? round($this->_size / $round) : $this->_size;
  }

  public function getOriginal()
  {
    if (!$this->_original)
    {
      $original = sfSympalAssetToolkit::createAssetObject($this->getRelativePathDirectory().'/'.sfSympalConfig::get('assets', 'originals_dir').'/'.$this->getName());
      if ($original->exists())
      {
        $this->_original = $original;
      }
    }
    return $this->_original;
  }

  public function delete()
  {
    if ($this->exists())
    {
      unlink($this->getPath());
    }
    if ($original = $this->getOriginal())
    {
      $original->delete();
    }
    return false;
  }

  public function save()
  {
    return true;
  }

  public function getDoctrineAsset()
  {
    if (!$this->_doctrineAsset)
    {
      $this->_doctrineAsset = Doctrine_Core::getTable('sfSympalAsset')
        ->createQuery('a')
        ->where('a.path = ?', $this->getRelativePathDirectory())
        ->andWhere('a.name = ?', $this->getName())
        ->fetchOne();
      if ($this->_doctrineAsset)
      {
        $this->_doctrineAsset->setAssetObject($this);
      }
    }
    return $this->_doctrineAsset;
  }

  public function rename($newName)
  {
    $this->move($this->getPathDirectory().'/'.$newName);
  }

  public function moveTo($path)
  {
    $this->move($path.'/'.$this->getName());
  }

  public function move($newPath)
  {
    $original = $this->getOriginal();
    rename($this->getPath(), $newPath);
    $this->setNewPath($newPath);
    if ($original)
    {
      $original->move($this->getPathDirectory().'/'.sfSympalConfig::get('assets', 'originals_dir').'/'.$this->getName());
    }
  }

  public function setNewPath($path)
  {
    $this->_filePath = str_replace($this->_rootPath, null, $path);
  }

  public function render($options = array())
  {
    $options = array_merge(sfSympalConfig::get('assets', 'default_render_options', array()));
    $options = sfApplicationConfiguration::getActive()->getEventDispatcher()->filter(new sfEvent($this, 'sympal.assets.filter_render_options'), $options)->getReturnValue();
    if (isset($options['renderer']) && $options['renderer'])
    {
      $renderer = new $options['renderer']($this, $options);
      return $renderer->render();
    }
    else if (isset($options['linked_thumbnail']))
    {
      return link_to($this->getThumbnailImage($options), $this->getUrl(), $options);
    }
    else if (isset($options['url']))
    {
      return $this->getUrl();
    }
    else if (isset($options['thumbnail_url']))
    {
      return $this->getThumbnailUrl();
    }
    else if (isset($options['icon_url']))
    {
      return $this->getIcon();
    }
    else if (isset($options['thumbnail']) && $options['thumbnail'] && isset($options['link']) && $options['link'])
    {
      return link_to($this->getThumbnailImage(array_merge($options, array('align' => 'left'))).' '.$this->getName(), $this->getUrl(), $options);
    }
    else if (isset($options['icon']) && $options['icon'] && isset($options['link']) && $options['link'])
    {
      return link_to($this->getIconImage(array_merge($options, array('align' => 'left'))).' '.$this->getName(), $this->getUrl(), $options);
    }
    else if (isset($options['embed']) && $options['embed'])
    {
      return $this->getEmbed($options);
    }
    else if (isset($options['link']) && $options['link'])
    {
      return $this->getLink($options);
    }
    else if (isset($options['icon']) && $options['icon'])
    {
      return $this->getIconImage($options);
    }
    else if (isset($options['thumbnail']) && $options['thumbnail'])
    {
      return $this->getThumbnailImage($options);
    }
    else
    {
      return $this->getEmbed($options);
    }
  }

  public function getIconImage($options = array())
  {
    return image_tag($this->getIcon(), $options);
  }

  public function getThumbnailImage($options = array())
  {
    return image_tag($this->getThumbnailUrl(), $options);
  }

  public function getThumbnailUrl()
  {
    return $this->getIcon();
  }

  public function getEmbed($options = array())
  {
    return $this->getLink($options);
  }

  public function getLink($options = array())
  {
    return link_to($this->getName(), $this->getUrl(), $options);
  }

  public function __toString()
  {
    return $this->getName();
  }
}