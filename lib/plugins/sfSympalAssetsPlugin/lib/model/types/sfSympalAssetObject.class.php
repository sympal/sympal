<?php

/**
 * Class that represents an uploaded asset - assists in rendering of that asset
 *
 * @package     sfSympalAssetPlugin
 * @subpackage  asset
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @since       2010-03-06
 * @version     svn:$Id$ $Author$
 */
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
    $_type = null;

  /**
   * Class constructor
   *
   * @param string $filePath The relative filepath of the asset
   *                         (e.g. my_file.jpg for SF_ROOT_DIR/web/uploads/my_file.jpg)
   */
  public function __construct($filePath)
  {
    $this->_filePath = $filePath;
    $this->_rootPath = sfConfig::get('sf_web_dir').sfSympalConfig::get('assets', 'root_dir', DIRECTORY_SEPARATOR.'uploads');

    // make sure an object isn't initialized with the wrong type subclass
    $type = $this->getTypeFromExtension();
    if ($this->getTypeFromExtension() != $this->_type && $this->_type !== 'file')
    {
      throw new sfException(sprintf('The file "%s" is not a %s', $filePath, $this->_type));
    }

    /*
     *  Set the type
     *
     * This is important because multiple types could all have the same
     * class. The above line makes sure that the type is legit for this
     * class, now we make sure that the type is set correctly
     */
    $this->_type = $type;
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

  /**
   * Whether or not this object is an image
   * @return boolean
   */
  public function isImage()
  {
    return ($this->getType() == 'image');
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
    return realpath($this->_rootPath.DIRECTORY_SEPARATOR.$this->getFilePath());
  }

  /**
   * Returns the path to the image relative to the upload directory
   * SF_ROOT_DIR/web/uploads/assets/my_file.txt => /assets/my_file.txt
   *
   * @return string
   */
  public function getRelativePath()
  {
    return str_replace($this->getRootPath(), null, $this->getPath());
  }

  /**
   * Returns the directory name relative to the uploads directory
   * SF_ROOT_DIR/web/uploads/assets/my_file.txt => /assets
   *
   * @return string
   */
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

  /**
   * Returns the url to this assets
   *
   * @return string
   */
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

  /**
   * Returns the filename for this asset
   *
   * @param boolean $withExtension Whether or not to return the filename with the extension
   * @return string The filename
   */
  public function getName($withExtension = true)
  {
    $this->_name = pathinfo($this->_filePath, PATHINFO_FILENAME);
    $extension = $this->getExtension();
    return $withExtension && $extension ? $this->_name.'.'.$extension : $this->_name;
  }

  /**
   * Returns the filesize of this file divided by the round variable
   *
   * @example
   * $kilobyteSize = $asset->getSize(1000);
   *
   * @param double $round A number used to divide the byte size
   * @return double
   */
  public function getSize($round = 1024)
  {
    if (!$this->_size)
    {
      $this->_size = filesize($this->getPath());
    }

    return $round >= 1 ? round($this->_size / $round) : $this->_size;
  }

  /**
   * Returns an sfSympalAssetObject instance representing an original
   * copy of this asset (if one exists)
   *
   * @param boolean $create Whether or not to try to create an original if it doesn't exist
   * @return sfSympalAssetObject
   */
  public function getOriginal($create = true)
  {
    if (!$this->_original)
    {
      $original = sfSympalAssetToolkit::createAssetObject($this->getRelativePathDirectory().DIRECTORY_SEPARATOR.sfSympalConfig::get('assets', 'originals_dir').DIRECTORY_SEPARATOR.$this->getName());
      if ($original->exists())
      {
        $this->_original = $original;
      }
      elseif ($create)
      {
        $this->getDoctrineAsset()->copyOriginal();
        
        // try to get the original, but don't create on failure
        $this->_original = $this->getOriginal(false);
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
    if ($original = $this->getOriginal(false))
    {
      $original->delete();
    }
    return false;
  }

  public function save()
  {
    return true;
  }

  /**
   * Retrieves the sfSympalAsset object that represents this asset.
   *
   * @return sfSympalAsset
   */
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
    $this->move($this->getPathDirectory().DIRECTORY_SEPARATOR.$newName);
  }

  public function moveTo($path)
  {
    $this->move($path.DIRECTORY_SEPARATOR.$this->getName());
  }

  /**
   * Move this asset to a new location
   *
   * @param string $newPath The absolute new path for this object
   */
  public function move($newPath)
  {
    $original = $this->getOriginal(false);

    mkdir(dirname($newPath), 0777, true);
    rename($this->getPath(), $newPath);
    $this->setNewPath($newPath);

    if ($original)
    {
      $original->move($this->getPathDirectory().DIRECTORY_SEPARATOR.sfSympalConfig::get('assets', 'originals_dir').DIRECTORY_SEPARATOR.$this->getName());
    }
  }

  /**
   * Ssets a new path for this asset. The path is relative to the
   * uploads directory.
   *
   * To move or rename an asset, use move() or rename()
   *
   * @param string $path The relative path to set as the asset
   */
  public function setNewPath($path)
  {
    $this->_filePath = str_replace($this->_rootPath, null, $path);
  }

  /**
   * Renders the asset, using a variety of options and taking into account
   * the type of this object
   *
   * @param array $options Rendering options, which include:
   *   * renderer - A class that will entirely handle the rendering of this asset
   *   * linked_thumbnail - A link to this asset with a thumbnail or icon as its body
   */
  public function render($options = array())
  {
    $options = array_merge(sfSympalConfig::get('assets', 'default_render_options', array()), $options);
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

  /**
   * Returns an image tag to an icon that represents this file type
   *
   * @return string
   */
  public function getIconImage($options = array())
  {
    return image_tag($this->getIcon(), $options);
  }

  /**
   * Returns an image tag to a thumbnail that represents this asset
   *
   * This may just be an icon representing the file type, or an actual
   * thumbnail (if this asset is actually an image)
   *
   * @return string The image tag
   */
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