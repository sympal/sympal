<?php

/**
 * Class responsible for actually processing the link syntaxes:
 * 
 * [myModel:1]
 * [myOtherModel:1 some_var="available in the partial"]
 * 
 * @package     sfSympalAssetsPlugin
 * @subpackage  replacer
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-01-30
 * @version     svn:$Id$ $Author$
 */
class sfSympalContentSyntaxObjectReplacer extends sfSympalContentSyntaxReplacer
{
  /**
   * @see sfSympalContentSyntaxReplacer
   */
  public function process($replacements, $content)
  {
    if (!$this->getOption('class'))
    {
      throw new sfException(sprintf('Cannot find key "class" under content_syntax_types "%s"', $this->type));
    }
    
    if (!$this->getOption('template'))
    {
      throw new sfException(sprintf('Cannot find key "template" under content_syntax_types "%s"', $this->type));
    }
    
    // retrieve the Doctrine_Collection of objects
    $objects = $this->_getObjects(array_keys($replacements));
    $objects = self::_buildObjects($objects);
    
    foreach ($replacements as $id => $replacement)
    {
      $object = $objects[$id];
      
      // provide the partial the object as the the name of its class
      $replacement['options'][$this->getOption('class')] = $object;
      
      // get the partial
      $ret = get_partial($this->getOption('template'), $replacement['options']);
      
      $content = str_replace($replacement['replace'], $ret, $content);
    }
    
    return $content;
  }
  
  /**
   * Returns a Doctrine_Collection of the objects to be rendered
   */
  protected function _getObjects($ids)
  {
    $tbl = Doctrine_Core::getTable($this->getOption('class'));
    if (method_exists($tbl, 'fetchObjectsForReplacer'))
    {
      return $tbl->fetchObjectsForReplacer($ids);
    }
    else
    {
      return $tbl->createQuery('s')
        ->whereIn('s.id', $ids)
        ->execute();
    }
  }
}