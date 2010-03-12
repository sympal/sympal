<?php

/**
 * Toolkit class for anything related to assets
 *
 * @package     sfSympalAssetsPlugin
 * @subpackage  util
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @since       2010-03-06
 * @version     svn:$Id$ $Author$
 */
class sfSympalAssetToolkit
{
  public static $iconsPath;

  /**
   * Returns the correct sfSympalAssetObject instance based on the extension
   * of the asset's filename
   *
   * @return sfSympalAssetObject
   */
  public static function createAssetObject($file)
  {
    $class = self::getClassFromExtension(self::getExtensionFromFile($file));

    return new $class($file);
  }

  public static function getClassFromExtension($extension)
  {
    $type = self::getTypeFromExtension($extension);
    $types = self::getFileTypes();

    return isset($types[$type]['class']) ? $types[$type]['class'] : 'sfSympalAssetFileObject';
  }

  public static function getTypeFromExtension($extension)
  {
    $extension = strtolower(trim($extension));
    $types = self::getFileTypes();
    foreach($types as $type => $data)
    {
      if(in_array($extension, $data['extensions']))
      {
        return $type;
      }
    }
    return 'file';
  }

  public static function getIconFromType($type)
  {
    $types = self::getFileTypes();
    $dir = self::getIconsDir();
    if (array_key_exists($type, $types))
    {
      $icon = array_key_exists('icon', $types[$type]) ? $types[$type]['icon'] : $type;
      return $dir.'/'.$icon.'.png';
    }
    return $dir.'/file.png';

  }

  public static function getNameFromFile($file)
  {
    $dorPosition = strrpos($file, '.');
    return $dorPosition ? substr($file, 0, $dorPosition) : $file;
  }

  public static function getExtensionFromFile($file)
  {
    return strtolower(substr(strrchr($file, '.'), 1));
  }

  /**
   * Returns an icon representing the file type of this asset
   *
   * @retun string the web-path to the icon
   */
  public static function getIconFromExtension($extension)
  {
    $dir = '/sfSympalAssetsPlugin/images/icons';
    $path = self::getIconsPath();
    if (file_exists($path.'/'.$extension.'.png'))
    {
      return $dir.'/'.$extension.'.png';
    }
    return self::getIconFromType(self::getTypeFromExtension($extension));
  }

  public static function getFileTypes()
  {
    return sfSympalConfig::get('assets', 'file_types', array());
  }

  /**
   * Returns the absolute path to the directory that holds the file
   * types icons
   *
   * @return string
   */
  public static function getIconsPath()
  {
    if (!self::$iconsPath)
    {
      self::$iconsPath = sfConfig::get('sf_web_dir').self::getIconsDir();
    }
    return self::$iconsPath;
  }

  public static function getIconsDir()
  {
    return '/sfSympalAssetsPlugin/images/icons';
  }

  public static function deleteRecursive($path)
  {
    $files = sfFinder::type('file')->in($path);
    foreach ($files as $file)
    {
      unlink($file);
    }
    $dirs = array_reverse(sfFinder::type('dir')->in($path));
    foreach ($dirs as $dir)
    {
      rmdir($dir);
    }
    return @rmdir($path);
  }
}