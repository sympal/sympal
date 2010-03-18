<?php

class sfSympalTaskClearCacheListener extends sfSympalListener
{
  /**
   * A boolean for if this process has been run yet, since the task.cache.clear
   * event will be called multiple times (once per environment) on a cache
   * clear (and this task only needs to be called once)
   */
  protected static $_isProcessed = false;
  
  public function getEventName()
  {
    return 'task.cache.clear';
  }

  public function run(sfEvent $event)
  {
    if (self::$_isProcessed)
    {
      return;
    }
    
    $event->getSubject()->logSection('sympal', 'Clearing web cache folder');

    $failures = array();
    $cacheDir = sfConfig::get('sf_web_dir').'/cache';
    if (is_dir($cacheDir))
    {
      $filesystem = $event->getSubject()->getFilesystem();
      
      $finder = sfFinder::type('file')->ignore_version_control()->discard('.sf');
      foreach ($finder->in($cacheDir) as $file)
      {
        @$filesystem->remove($file);
        
        if (file_exists($file))
        {
          $failures[] = $file;
        }
      }
    }
    self::$_isProcessed = true;
    
    if (count($failures) > 0)
    {
      $event->getSubject()->logBlock(array_merge(
        array('Could not clear cache on the following files:', ''),
        array_map(create_function('$f', 'return \' - \'.sfDebug::shortenFilePath($f);'), $failures)
      ), 'ERROR_LARGE');
    }
  }
}