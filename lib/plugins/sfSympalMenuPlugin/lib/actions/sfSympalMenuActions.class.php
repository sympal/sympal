<?php

/**
 * Extension of sfActions
 * 
 * @package     sfSympalMenuPlugin
 * @subpackage  actions
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-04-01
 * @version     svn:$Id$ $Author$
 */
class sfSympalMenuActions extends sfSympalExtendClass
{
  /**
   * Clear the menu cache from your actions
   *
   * @return void
   */
  public function clearMenuCache()
  {
    $files = glob(sfConfig::get('sf_cache_dir').'/'.sfConfig::get('sf_app').'/*/SYMPAL_MENU_*.cache');
    foreach ((array) $files as $file)
    {
      unlink($file);
    }
  }
}