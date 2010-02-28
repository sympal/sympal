<?php
/**
 * Subclass of the main replacer class, which allows some special functionality
 * when we know that the content is linked to a sfSympalContentSlot object
 * 
 * @package     sfSympalContentSyntaxPlugin
 * @subpackage  core
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-01-30
 * @version     svn:$Id$ $Author$
 */

class sfSympalContentSlotReplacer extends sfSympalContentReplacer
{
  protected
    $_content;
  
  public function __construct(sfSympalContent $content)
  {
    $this->_content = $content;
    
    parent::__construct();
  }
  
  /*
   * The main transformer method that is called if a slot type is processed
   * via the "replacer" transformer.
   * 
   * @param string $content The raw content to be processed
   * @param sfSympalContentSlotTransformer $transformer
   */
  public static function transformSlotContent($content, sfSympalContentSlotTransformer $transformer)
  {
    $replacer = new self($transformer->getContentSlot()->getContentRenderedFor());
    
    return $replacer->replace($content);
  }
  
  /**
   * @return sfSympalContent
   */
  public function getContent()
  {
    return $this->_content;
  }
}