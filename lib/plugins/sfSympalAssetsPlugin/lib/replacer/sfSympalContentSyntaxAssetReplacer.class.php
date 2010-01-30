<?php

/**
 * Class responsible for actually processing the asset syntaxes:
 * 
 * [asset:1]
 * [asset:1 alt="my cool image"]
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
    
    foreach ($replacements as $id => $replacement)
    {
      $assetObject = $assetObjects[$id];
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
  protected function _getAssetObjects($ids)
  {
    if ($this->_replacer instanceof sfSympalContentSlotReplacer)
    {
      $sympalContent = $this->_replacer->getContent();
      if (array_diff($ids, $sympalContent->Assets->getPrimaryKeys()) || array_diff($sympalContent->Assets->getPrimaryKeys(), $ids))
      {
        $assetObjects = $this->_getQueryForAssetObjects($ids)->execute();
        
        foreach ($assetObjects as $assetObject)
        {
          $sympalContent->Assets[] = $assetObject;
        }
        
        $sympalContent->save();
      }

      return $sympalContent->Assets;
    }
    else
    {
      return $this->_getQueryForAssetObjects($ids)->execute();
    }
  }
  
  /**
   * Returns the query that should be used if we need to query out
   * and get a collection of sfSympalContent objects
   */
  protected function _getQueryForAssetObjects($ids)
  {
    $q = Doctrine_Core::getTable('sfSympalAsset')
      ->createQuery()
      ->from('sfSympalAsset a')
      ->whereIn('a.id', array_unique($ids));
    
    return $q;
  }
}