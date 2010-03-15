<?php

/**
 * Handles the processing of an sfSympalContentSlot through its transformers
 * 
 * This takes in a sfSympalContentSlot object and outputs the fully
 * transformed content representing that slot.
 * 
 * This class is setup for a tokenized system that would allow for the
 * caching of the overall transformation process, while still keeping
 * dynamic pieces outside of anything that would go into the cache. The
 * cache system is not currently in place, pending more discussion. The
 * transformations themselves would need to be intelligent enough to return
 * the content and tokenCallbacks in the correct way so that caching is possible.
 * 
 * @package     sfSympalRenderingPlugin
 * @subpackage  transformer
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-02-27
 * @version     svn:$Id$ $Author$
 */
class sfSympalContentSlotTransformer
{
  /**
   * @var sfSympalContentSlot
   */
  protected $_contentSlot;
  
  /**
   * @var string
   * 
   * The processed content, potentially with tokens that will need replacing
   */
  protected $_transformedContent;
  
  /**
   * @var array
   * 
   * An array of callbacks that will be used to replace any tokens in
   * the _processedContent string. The end result is the fully-processed content
   */
  protected $_tokenCallbacks = array();
  
  /**
   * Class constructor
   * 
   * @param sfSympalContentSlot $contentSlot The content slot that will be transformed
   */
  public function __construct(sfSympalContentSlot $contentSlot)
  {
    $this->_contentSlot = $contentSlot;
  }
  
  /**
   * The public-facing method that will return the transformed content
   * 
   * @param The value to transform
   * @return string The transformed content
   */
  public function render($value)
  {
    if ($value !== null)
    {
      $this->setTransformedContent($value);
    }
    
    $this->process();
    
    $replacements = $this->getTokenReplacementValues();
    
    return strtr($this->getTransformedContent(), $replacements);
  }
  
  /**
   * Processes the content through all of the transformers and ultimately
   * sets the _transformedContent and _tokenCallbacks properties
   * 
   * Each individual transformation callback will modify the
   * _transformedContent and _tokenCallbacks properties, from which we
   * can finally generate the final content
   * 
   * @return void
   */
  protected function process()
  {
    foreach ($this->getTransformerCallbacks() as $callback)
    {
      $this->setTransformedContent(call_user_func($callback, $this->getTransformedContent(), $this));
    }
  }
  
  /**
   * Returns the transformed content
   * 
   * This is used by the transformers to retrieve the current processed
   * content for further transformation
   */
  public function getTransformedContent()
  {
    return $this->_transformedContent;
  }
  
  /**
   * Sets the transformed content
   * 
   * This is used by the transformers to put in their transformed content
   */
  public function setTransformedContent($content)
  {
    $this->_transformedContent = $content;
  }
  
  /**
   * Add token callback
   * 
   * This is used by the transformers to notify this class of a token that
   * needs to be dynamically replaced via a callback
   * 
   * @param string $token The string token that this will replace
   * @param callback $callback Any valid callback
   * @param array $args Arguments to pass to the callback
   */
  public function addTokenCallback($token, $callback, $args = array())
  {
    $this->_tokenCallbacks[$token] = array(
      'callback'  => $callback,
      'args'      => $args,
    );
  }
  
  /**
   * Iterates through all of the _tokenCallbacks entries and creates an
   * array with the actual value represented by those callbacks.
   * 
   * The final array is very simple and will be used for strtr:
   * array(
   *   'token1' => 'some processed value',
   *   'token2' => 'another value',
   * )
   * 
   * @return array
   */
  protected function getTokenReplacementValues()
  {
    $tokenReplacements = array();
    foreach ($this->_tokenCallbacks as $token => $tokenCallback)
    {
      $tokenReplacements[$token] = call_user_func_array($tokenCallback['callback'], $tokenCallback['args']);
    }
    
    return $tokenReplacements;
  }
  
  /**
   * Returns the transformer callables for this slot
   * 
   * @return array
   */
  protected function getTransformerCallbacks()
  {
    $config = sfSympalConfig::get('content_slot_types', $this->getContentSlot()->type);
    
    $transformers = isset($config['transformers']) ? $config['transformers'] : array();
    
    $transformersConfig = sfSympalConfig::get('slot_transformers');
    $transformerCallables = array();
    foreach($transformers as $name)
    {
      if (!isset($transformersConfig[$name]))
      {
        throw new sfException(sprintf('Invalid slot transformer "%s"', $name));
      }
      
      $transformerCallables[] = $transformersConfig[$name];
    }
    
    return $transformerCallables;
  }
  
  /**
   * @return sfSympalContentSlot
   */
  public function getContentSlot()
  {
    return $this->_contentSlot;
  }
}