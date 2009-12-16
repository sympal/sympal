<?php

class sfSympalCache
{
  protected
    $_sympalConfiguration,
    $_contentTypes = null,
    $_helperAutoload = null,
    $_modules = null,
    $_layouts = null;

  public function __construct(sfSympalConfiguration $sympalConfiguration)
  {
    $this->_sympalConfiguration = $sympalConfiguration;
    $this->_projectConfiguration = $sympalConfiguration->getProjectConfiguration();
    $this->_cacheDriver = call_user_func_array(sfSympalConfig::get('get_cache_driver_callback'), array($this));

    $this->primeCache();
  }

  public static function getCacheDriver(sfSympalCache $cache)
  {
    return new sfFileCache(
      array('cache_dir' => sfConfig::get('sf_cache_dir').'/'.sfConfig::get('sf_app').'/'.sfConfig::get('sf_environment')
    ));
  }

  public function clear()
  {
    $this->_contentTypes = null;
    $this->_helperAutoload = null;
    $this->_modules = null;
    $this->_layouts = null;
    $this->_cacheDriver->set('primed', false);
  }

  public function primeCache($force = false)
  {
    if ($this->_cacheDriver->has('primed') && !$force)
    {
      return true;
    }

    $this->clear();
    $this->_writeContentTypesCache();
    $this->_writeHelperAutoloadCache();
    $this->_writeModulesCache();
    $this->_writeLayoutsCache();

    $this->_cacheDriver->set('primed', true);
  }

  public function resetRouteCache()
  {
    $cachePath = sfConfig::get('sf_cache_dir').'/'.sfConfig::get('sf_app').'/'.sfConfig::get('sf_environment').'/routes.cache.yml';
    unlink($cachePath);
    $context = sfContext::getInstance();
    $configCache = $context->getConfigCache();
    unlink($configCache->getCacheName('config/routing.yml'));
    $context->getRouting()->loadConfiguration();
  }

  public function getLayouts()
  {
    if ($this->_layouts === null)
    {
      $this->_layouts = $this->get('layouts');
    }

    return $this->_layouts;
  }

  public function getModules()
  {
    if ($this->_modules === null)
    {
      $this->_modules = $this->get('modules');
    }

    return $this->_modules;
  }

  public function getContentTypes()
  {
    if ($this->_contentTypes === null)
    {
      $this->_contentTypes = $this->get('content_types');
    }

    return $this->_contentTypes;
  }

  public function getHelperAutoload()
  {
    if ($this->_helperAutoload === null)
    {
      $this->_helperAutoload = $this->get('helper_autoload');
    }

    return $this->_helperAutoload;
  }

  protected function _writeHelperAutoloadCache()
  {
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
    $this->set('helper_autoload', $cache);
  }

  protected function _writeContentTypesCache()
  {
    try {
      $typesArray = array();
      $contentTypes = Doctrine_Core::getTable('ContentType')->findAll();
      foreach ($contentTypes as $contentType)
      {
        $typesArray[$contentType['id']] = $contentType['name'];
      }
      $this->set('content_types', $typesArray);
    } catch (Exception $e) {}
  }

  protected function _writeModulesCache()
  {
    $modules = array();
    $plugins = $this->_sympalConfiguration->getPluginPaths();

    foreach ($plugins as $plugin => $path)
    {
      $path = $path . '/modules';
      $find = glob($path . '/*');

      if (is_array($find))
      {
        foreach ($find as $module)
        {
          if (is_dir($module))
          {
            $info = pathinfo($module);
            $modules[] = $info['basename'];
          }
        }
      }
    }
    $this->set('modules', $modules);
  }

  protected function _writeLayoutsCache()
  {
    $layouts = array();
    foreach ($this->_sympalConfiguration->getPluginPaths() as $plugin => $path)
    {
      $path = $path.'/templates';
      $find = glob($path.'/*.php');
      if (is_array($find))
      {
        $layouts = array_merge($layouts, $find);
      }
    }

    $find = glob(sfConfig::get('sf_app_dir').'/templates/*.php');
    if (is_array($find))
    {
      $layouts = array_merge($layouts, $find);
    }

    $layoutsCache = array();
    foreach ($layouts as $path)
    {
      $info = pathinfo($path);
      $name = $info['filename'];
      // skip partial/component templates
      if ($name[0] == '_')
      {
        continue;
      }
      $path = str_replace(sfConfig::get('sf_root_dir').'/', '', $path);
      $layoutsCache[$path] = $name;
    }
    $this->set('layouts', $layoutsCache);
  }

  public function set($key, $data, $lifeTime = null)
  {
    return $this->_cacheDriver->set($key, serialize($data), $lifeTime);
  }

  public function get($key)
  {
    return unserialize($this->_cacheDriver->get($key));
  }

  public function __call($method, $arguments)
  {
    return call_user_func_array(array($this->_cacheDriver, $method), $arguments);
  }
}