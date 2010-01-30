<?php

/**
 * Abstract class that handles the replacement of one type of syntax type.
 * 
 * @package     sfSympalContentSyntaxPlugin
 * @subpackage  replacer
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-01-30
 * @version     svn:$Id$ $Author$
 */

abstract class sfSympalContentSyntaxReplacer
{
  protected
    $_replacer,
    $_type,
    $_options;
  
  /**
   * Class constructor
   * 
   * @param sfSympalContentReplacer $replacer The replacer class that is overseeing the entire replacement process
   * @param string                  $type     The type (asset, link) that is being replaced
   * @param array                   $options  An array of options
   */
  public function __construct(sfSympalContentReplacer $replacer, $type, $options = array())
  {
    $this->_replacer = $replacer;
    $this->_type = $type;
    $this->_options = $options;
  }
  
  /**
   * The main function that is called to run the replacement
   * 
   * @see sfSympalContentReplacer::_parseSyntaxes
   * 
   * @param   array   $replacements The array of replacements to process
   * @param   string  $content      The raw content to make the replacements to
   * 
   * @return  string The processed/replaced content
   */
  abstract public function process($replacements, $content);
  
  /**
   * Remaps a doctrine collection so that the keys of the colleciton "array"
   * are the ids of the underlying objects. This makes the collection
   * easier to deal with then replacing objects
   * 
   * @return Doctrine_Collection
   */
  protected static function _buildObjects(Doctrine_Collection $collection)
  {
    $objects = new Doctrine_Collection($collection->getTable());
    foreach ($collection as $key => $value)
    {
      $objects[$value->id] = $value;
    }

    return $objects;
  }
  
  /**
   * @return mixed
   */
  public function getOption($name, $default = null)
  {
    return isset($this->_options[$name]) ? $this->_options[$name] : $default;
  }
}