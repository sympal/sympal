<?php

/**
 * Class responsible for actually processing the asset syntaxes:
 * 
 * [asset:my_file.gif]
 * [asset:my_file.gif alt="my cool image"]
 * 
 * @package     sfSympalAssetsPlugin
 * @subpackage  replacer
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-01-30
 * @version     svn:$Id$ $Author$
 */
class sfSympalContentSyntaxAssetReplacer extends sfSympalContentSyntaxReplacer
{
  /**
   * @see sfSympalContentSyntaxReplacer
   */
  public function process($replacements, $content)
  {
    $assetObjects = $this->_getAssetObjects(array_keys($replacements));
    $assetObjects = self::_buildObjects($assetObjects);
    
    foreach ($replacements as $slug => $replacement)
    {
      $assetObject = $assetObjects[$slug];
      $content = $assetObject->filterContent($content, $replacement['replace'], $replacement['options']);
    }
    
    return $content;
  }
  
  /**
   * Retrieves the Doctrine_Collection of asset objects.
   * 
   * If the core replacer is of type sfSympalContentSlotReplacer then
   * we have access to a sfSympalContent object to which we'll want to
   * relate these sfSympalAsset objects
   */
  protected function _getAssetObjects($slugs)
  {
    if ($this->_replacer instanceof sfSympalContentSlotReplacer)
    {
      $sympalContent = $this->_replacer->getContent();
      $currentSlugs = $sympalContent->Assets->getSlugs();
      asort($slugs);

      if (array_diff($slugs, $currentSlugs) || array_diff($currentSlugs, $slugs))
      {
        $assetObjects = $this->_getQueryForAssetObjects($slugs)->execute();
        
        foreach ($assetObjects as $assetObject)
        {
          $sympalContent->Assets[] = $assetObject;
        }
        $sympalContent->disableSearchIndexUpdateForSave();
        $sympalContent->save();
      }

      return $sympalContent->Assets;
    }
    else
    {
      return $this->_getQueryForAssetObjects($slugs)->execute();
    }
  }
  
  /**
   * Returns the query that should be used if we need to query out
   * and get a collection of sfSympalContent objects
   */
  protected function _getQueryForAssetObjects($slugs)
  {
    $q = Doctrine_Core::getTable('sfSympalAsset')
      ->createQuery()
      ->from('sfSympalAsset a')
      ->whereIn('a.slug', array_unique($slugs))
      ->orderBy('a.slug ASC');

    return $q;
  }
}