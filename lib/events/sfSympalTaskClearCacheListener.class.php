<?php

class sfSympalTaskClearCacheListener extends sfSympalListener
{
  public function getEventName()
  {
    return 'task.clear_cache';
  }

  public function run(sfEvent $event)
  {
    $event->getSubject()->logSection('sympal', 'Clearing web cache folder');

    $cacheDir = sfConfig::get('sf_web_dir').'/cache';
    if (is_dir($cacheDir))
    {
      $event->getSubject()->getFilesystem()->remove(sfFinder::type('file')->ignore_version_control()->discard('.sf')->in($cacheDir));
    }
  }
}