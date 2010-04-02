<?php

/**
 * Helper to assist with combining and minifying assets
 * 
 * @package     sfSympalMinifyPlugin
 * @subpackage  helper
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-04-01
 * @version     svn:$Id$ $Author$
 */

/**
 * Call this method in your layouts before stylesheets and javascripts html
 * are included. It will minify all your files and use them instead in production.
 * Configurable in your config/app.yml
 *
 * @return void
 */
function sympal_minify()
{
  if (sfSympalConfig::get('minifier', 'enabled', true))
  {
    $minifier = new sfSympalMinifier(
      sfContext::getInstance()->getResponse(),
      sfContext::getInstance()->getRequest()
    );
    $minifier->minify();
  }
}