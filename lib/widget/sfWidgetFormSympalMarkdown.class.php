<?php

/**
 * Special widget for rendering markdown textareas
 * 
 * @package     sfSympalPlugin
 * @subpackage  widget
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-02-24
 * @version     svn:$Id$ $Author$
 */

class sfWidgetFormSympalMarkdown extends sfWidgetFormTextarea
{  
  /**
   * @see sfWidget
   */
  public function getStylesheets()
  {
    return array(
      '/sfSympalPlugin/markitup/skins/markitup/style.css' => '',
      '/sfSympalPlugin/markitup/sets/markdown/style.css' => '',
    );
  }
  
  /**
   * @see sfWidget
   */
  public function getJavascripts()
  {
    return array(
      '/sfSympalPlugin/markitup/jquery.markitup.js',
      '/sfSympalPlugin/markitup/sets/markdown/set.js',
    );
  }
}