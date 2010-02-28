<?php

/**
 * Core class that handles the processing and replacing of raw text
 * 
 * @package     sfSympalContentSyntaxPlugin
 * @subpackage  core
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-01-30
 * @version     svn:$Id$ $Author$
 */

class sfSympalContentReplacer
{
  public function __construct()
  {
  }
  
  /**
   * Processes the given content and returns it
   * 
   * @param   string $content The raw content to process
   * @return  string The processed content
   */
  public function replace($content)
  {
    if ($parsed = $this->_parseSyntaxes($content))
    {
      // iterate through all of the replacement types
      foreach($parsed as $type => $replacements)
      {
        $content = $this->processReplacerType($type, $replacements, $content);
      }
    }

    return $content;
  }
  
  /**
   * Processes the array of replacements for the given type
   */
  protected function processReplacerType($type, $replacements, $content)
  {
    $config = sfSympalConfig::get('content_syntax_types', $type, array());
    
    if (!isset($config['replacer_class']))
    {
      throw new sfException(sprintf('No replacer_class defined for "%s" key in content_syntax_types', $type));
    }
    
    $class = $config['replacer_class'];
    $options = isset($config['replacer_options']) ? $config['replacer_options'] : array();
    $replacer = new $class($this, $type, $options);
      
    return $replacer->process($replacements, $content);
  }
  
  /**
   * Searches through the content and extracts out any matches. The return
   * value is a formatted array of what needs to be replaced
   * 
   * Returned syntax will look like this:
   *   array(
   *     'link' => array(
   *       'some-page' => array('options' => array(), 'replace' => '[link:some-page]'),
   *       'other-page' => array('options' => array('option' => 'value'), 'replace' => '[link:other-page option=value]'),
   *     ), asset => array(
   *       'my-asset' => array('options' => array(), 'replace' => '[asset:my-asset]'),
   *     ),
   *   )
   * 
   * @return array
   */
  private function _parseSyntaxes($content)
  {
    // create the replacement string (e.g. link|asset|myObject)
    $replacementString = implode('|', array_keys(sfSympalConfig::get('content_syntax_types')));
    preg_match_all("/\[($replacementString):(.*?)\]/", $content, $matches);

    if (isset($matches[0]) && $matches[0])
    {
      $replacements = array();
      $types = $matches[1];
      $bodies = $matches[2];
      
      foreach($types as $type)
      {
        $replacements[$type] = array();
      }
      
      /*
       * body matches (e.g. "3" or "5 option=value")
       */
      foreach ($bodies as $key => $body)
      {
        // use the key to find the corresponding type
        $typeKey = $types[$key];
        
        $e = explode(' ', $body);
        $slug = $e[0];
        
        $replacements[$typeKey][$slug] = array(
          'options' => _parse_attributes(substr($body, strlen($e[0]))),
          'replace' => $matches[0][$key],
        );
      }
      
      return $replacements;
    } else {
      return false;
    }
  }
}