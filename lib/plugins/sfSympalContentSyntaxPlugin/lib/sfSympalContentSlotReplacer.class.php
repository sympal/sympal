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
   * Responds to the sympal.content_renderer.filter_slot_content filter
   * event. This creates a new replacer and returns the processed content
   */
  public static function listenToFilterSlotContent(sfEvent $event, $content)
  {
    $replacer = new self($event->getSubject()->getContentRenderedFor());
    
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