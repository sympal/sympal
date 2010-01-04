<?php

class sfSympalAssetToolkit
{
  protected static $_typeClassMap = array(
    'image' => 'sfSympalAssetImageObject',
    'file' => 'sfSympalAssetFileObject',
    'video' => 'sfSympalAssetVideoObject'
  );

  public static $iconsPath;

  public static function createAssetObject($file)
  {
    $extension = sfSympalAssetToolkit::getExtensionFromFile($file);
    $type = sfSympalAssetToolkit::getTypeFromExtension($extension);
    $class = isset(self::$_typeClassMap[$type]) ? self::$_typeClassMap[$type] : 'sfSympalAssetsFileObject';
    return new $class($file);
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

  public static function getIconFromExtension($extension)
  {
    $dir = '/sfSympalAssetsPlugin/images/icons';
    $path = self::getIconsPath();
    if (file_exists(sfConfig::get('sf_web_dir').'/'.$path.'/'.$extension.'.png'))
    {
      return $dir.'/'.$extension.'.png';
    }
    return self::getIconFromType(self::getTypeFromExtension($extension));
  }

  public static function getFileTypes()
  {
    return sfSympalConfig::get('assets', 'file_types', array());
  }

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