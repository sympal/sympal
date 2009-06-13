<?php

class sfSympalCache
{
  protected
    $_sympalConfiguration;

  protected static
    $_contentTypes = null;

  public function __construct(sfSympalConfiguration $sympalConfiguration)
  {
    $this->_sympalConfiguration = $sympalConfiguration;
    $this->_projectConfiguration = $sympalConfiguration->getProjectConfiguration();

    $this->primeCache();
  }

  public static function getContentTypes()
  {
    if (is_null(self::$_contentTypes))
    {
      $cachePath = sfConfig::get('sf_cache_dir').'/sympal/content_types.cache';
      if (file_exists($cachePath))
      {
        self::$_contentTypes = unserialize(file_get_contents($cachePath));
      }
    }

    return self::$_contentTypes;
  }

  public function primeCache($force = false)
  {
    if (file_exists(sfConfig::get('sf_cache_dir').'/sympal/cache_primed.cache') && !$force)
    {
      return true;
    }

    if (!is_dir($path = sfConfig::get('sf_cache_dir').'/sympal'))
    {
      mkdir($path, 0777, true);
    }

    $this->_writeContentTypesCache();
    $this->_writeHelperAutoloadCache();

    touch(sfConfig::get('sf_cache_dir').'/sympal/cache_primed.cache');
  }

  protected function _writeHelperAutoloadCache()
  {
    $cachePath = sfConfig::get('sf_cache_dir').'/sympal/helper_autoload.cache';
    $cache = array();
    $dirs = $this->_projectConfiguration->getHelperDirs();
    foreach ($dirs as $dir)
    {
      $helpers = sfFinder::type('file')->name('*Helper.php')->in($dir);
      foreach ($helpers as $helper)
      {
        $lines = file($helper);
        foreach ($lines as $line)
        {
          preg_match("/function (.*)\(/", $line, $matches);
          if ($matches)
          {
            $function = $matches[1];
            $e = explode('(', $function);
            $function = $e[0];
            $cache[$function] = $helper;
          }
        }
      }
    }
    file_put_contents($cachePath, serialize($cache));
  }

  protected function _writeContentTypesCache()
  {
    try {
      $cachePath = sfConfig::get('sf_cache_dir').'/sympal/content_types.cache';
      $typesArray = array();
      $contentTypes = Doctrine::getTable('ContentType')->findAll();
      foreach ($contentTypes as $contentType)
      {
        $typesArray[$contentType['id']] = $contentType['name'];
      }
      file_put_contents($cachePath, serialize($typesArray));
    } catch (Exception $e) {}
  }
}