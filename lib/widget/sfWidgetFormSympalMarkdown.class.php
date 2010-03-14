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
   * Overridden to add the necessary javascript for creating the markdown
   * editor.
   * 
   * @see sfWidgetFormTextarea
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $widget = parent::render($name, $value, $attributes, $errors);
    
    $id = $this->generateId($name, $value);
    
    $javascript = sprintf("
<script type=\"text/javascript\">
  $(document).ready(function(){
    jQuery('#%s:not(.markItUpEditor)').markItUp(mySettings);
  });
</script>
    ",
    $id);
    
    return $widget.$javascript;
  }
  
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