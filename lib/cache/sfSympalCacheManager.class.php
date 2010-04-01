<?php

/**
 * Sympal cache managing class. All cache operations write through this class:
 *
 *  * Doctrine ORM Cache Driver
 *  * Routes
 *  * Layouts
 *  * Modules
 *  * Content Types
 *  * Helpers
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 * @author Ryan Weaver <ryan@thatsquality.com>
 */
class sfSympalCacheManager
{
  protected
    $_dispatcher;
  
  protected
    $_helperAutoload = null,
    $_modules = null,
    $_layouts = null;

  /**
   * Instantiate the sfSympalCache instance and prime the cache for this Sympal
   * project
   *
   * @see sfSympalConfiguration
   * @see sfSympalPluginConfiguration
   * @param sfSympalConfiguration $sympalConfiguration
   */
  public function __construct(sfEventDispatcher $dispatcher, sfCache $cacheDriver)
  {
    $this->_dispatcher = $dispatcher;
    $this->_cacheDriver = $cacheDriver;

    $this->primeCache();
  }

  /**
   * Configured default callable in config/app.yml to return the 
   * sfCache driver to use to store cache entries for the passed sfSympalCache instance
   *
   * @param sfSympalCache $cache
   * @return sfCache $driver A instance of a sfCache driver
   */
  public static function getCacheDriver(sfSympalCache $cache)
  {
    return new sfFileCache(
      array('cache_dir' => sfConfig::get('sf_cache_dir').'/'.sfConfig::get('sf_app').'/'.sfConfig::get('sf_environment')
    ));
  }

  /**
   * Get the Doctrine cache driver to use for Doctrine query and result cache
   *
   * @return Doctrine_Cache_Driver $driver
   */
  public static function getOrmCacheDriver()
  {
    if (extension_loaded('apc'))
    {
      return new Doctrine_Cache_Apc(array('prefix' => 'doctrine'));
    }
    else
    {
      return new Doctrine_Cache_Array();
    }
  }

  /**
   * Clear the cache and force it to be primed again
   *
   * @return void
   */
  public function clear()
  {
    $this->_helperAutoload = null;
    $this->_modules = null;
    $this->_layouts = null;
    $this->_cacheDriver->set('primed', false);
  }

  /**
   * Prime the cache for this sfSympalCache instance
   * 
   * Notifies the sympal.cache.prime event, allowing for any class to
   * hook in to the cache prime
   *
   * @param boolean $force Force it to prime the cache regardless of whether or not it has been primed already
   * @return void
   */
  public function primeCache($force = false)
  {
    if ($this->_cacheDriver->has('primed') && !$force)
    {
      return;
    }

    $this->clear();
    
    /**
     * @TODO reimplement this in the proper location
     */
    //$this->_writeHelperAutoloadCache();
    //$this->_writeModulesCache();
    //$this->_writeLayoutsCache();
    
    $this->_dispatcher->notify(new sfEvent($this, 'sympal.cache.prime'));

    $this->_cacheDriver->set('primed', true);
  }

  /**
   * Reset the routing cache
   *
   * @return void
   */
  public function resetRouteCache()
  {
    $cachePath = sfConfig::get('sf_cache_dir').'/'.sfConfig::get('sf_app').'/'.sfConfig::get('sf_environment').'/routes.cache.yml';
    if (file_exists($cachePath))
    {
      unlink($cachePath);
    }

    $context = sfContext::getInstance();
    $configCache = $context->getConfigCache();

    if (file_exists($cachePath = $configCache->getCacheName('config/routing.yml')))
    {
      unlink($cachePath);
    }

    $context->getRouting()->loadConfiguration();
  }

  /**
   * Get the cached layouts array
   *
   * @return array $layouts
   */
  public function getLayouts()
  {
    throw new sfException('@TODO - Must be reimplemented');
    if ($this->_layouts === null)
    {
      $this->_layouts = $this->get('layouts');
    }

    return $this->_layouts;
  }

  /**
   * Get the cached helper methods and paths
   *
   * @return array $helpers
   */
  public function getHelperAutoload()
  {
    throw new sfException('@TODO Needs to be reimplemented');
    if ($this->_helperAutoload === null)
    {
      $this->_helperAutoload = $this->get('helper_autoload');
    }

    return $this->_helperAutoload;
  }

  /**
   * Write the helper autoload cache
   * 
   * This caches an array of function names (e.g. url_for) and the file
   * that contains that method (e.g. UrlHelper.php)
   *
   * @return void
   */
  protected function _writeHelperAutoloadCache()
  {
    throw new sfException('@TODO Needs to be reimplemented');
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

  /**
   * @see sfCache::remove()
   */
  public function remove($key)
  {
    return $this->_cacheDriver->remove($key);
  }

  /**
   * @see sfCache::set()
   */
  public function set($key, $data, $lifeTime = null)
  {
    return $this->_cacheDriver->set($key, serialize($data), $lifeTime);
  }

  /**
   * @see sfCache::has()
   */
  public function has($key)
  {
    return $this->_cacheDriver->has($key);
  }

  /**
   * @see sfCache::get()
   */
  public function get($key)
  {
    return unserialize($this->_cacheDriver->get($key));
  }

  /**
   * Forward any unknown method calls to the sfCache driver instance
   *
   * @param string $method
   * @param array $arguments
   * @return mixed $return
   */
  public function __call($method, $arguments)
  {
    return call_user_func_array(array($this->_cacheDriver, $method), $arguments);
  }
}