<?php

/**
 * Class responsible for actually processing the link syntaxes:
 * 
 * [link:1]
 * [link:1 title="click me"]
 * 
 * @package     sfSympalAssetsPlugin
 * @subpackage  replacer
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-01-30
 * @version     svn:$Id$ $Author$
 */
class sfSympalContentSyntaxLinkReplacer extends sfSympalContentSyntaxReplacer
{
  /**
   * @see sfSympalContentSyntaxReplacer
   */
  public function process($replacements, $content)
  {
    $contentObjects = $this->_getContentObjects(array_keys($replacements));
    $contentObjects = self::_buildObjects($contentObjects);
    
    foreach ($replacements as $slug => $replacement)
    {
      $contentObject = $contentObjects[$slug];
      
      $urlOnly = isset($replacement['options']['url']) ? $replacement['options']['url'] : false;
      unset($replacement['options']['url']);
      
      if ($urlOnly)
      {
        $content = str_replace($replacement['replace'], url_for($contentObject->getRoute(), $replacement['options']), $content);
      }
      else
      {
        $label = isset($replacement['options']['label']) ? $this->_replacer->replace($replacement['options']['label']) : null;
        unset($replacement['options']['label']);
        
        $content = str_replace($replacement['replace'], link_to($label, $contentObject->getRoute(), $replacement['options']), $content);
      }
    }
    
    return $content;
  }
  
  /**
   * Retrieves the Doctrine_Collection of sfSympalContent objects.
   * 
   * If the core replacer is of type sfSympalContentSlotReplacer then
   * we have access to a sfSympalContent object to which we'll want to
   * relate these sfSympalContent objects
   */
  protected function _getContentObjects($slugs)
  {
    if ($this->_replacer instanceof sfSympalContentSlotReplacer)
    {
      $sympalContent = $this->_replacer->getContent();
      $currentSlugs = $sympalContent->Links->getSlugs();
      asort($slugs);

      if (array_diff($slugs, $currentSlugs) || array_diff($currentSlugs, $slugs))
      {
        $contentObjects = $this->_getQueryForContentObjects($slugs)->execute();
        
        foreach ($contentObjects as $contentObject)
        {
          $sympalContent->Links[] = $contentObject;
        }
        $sympalContent->disableSearchIndexUpdateForSave();
        $sympalContent->save();
      }

      return $sympalContent->Links;
    }
    else
    {
      return $this->_getQueryForContentObjects($slugs)->execute();
    }
  }
  
  /**
   * Returns the query that should be used if we need to query out
   * and get a collection of sfSympalContent objects
   */
  protected function _getQueryForContentObjects($slugs)
  {
    $q = Doctrine_Core::getTable('sfSympalContent')
      ->createQuery('c')
      ->select('c.*, t.*')
      ->from('sfSympalContent c')
      ->innerJoin('c.Type t')
      ->innerJoin('c.Site s')
      ->whereIn('c.slug', array_unique($slugs))
      ->andWhere('s.slug = ?', sfConfig::get('sf_app'))
      ->orderBy('c.slug ASC');

    if (sfSympalConfig::isI18nEnabled('sfSympalContent'))
    {
      $q->leftJoin('c.Translation ct');
    }
    
    return $q;
  }
}