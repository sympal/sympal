<?php

/**
 * Class responsible for parsing and replacing syntaxes for linking to assets,
 * embedding assets, linking to other content, etc.
 *
 * Examples:
 *
 *  * [asset:1 label="Linking to content id #1"]
 *  * [link:123]
 *  * [asset:1 link=true]
 *  * [asset:1 embed=true]
 */
class sfSympalAssetReplacer
{
  private
    $_slot,
    $_content;
  
  /**
   * Array of possible keys (e.g. asset, link) that should be searched
   * for along with the callback that should be called to make that replacement
   * 
   * 'link'   => array('sfSympalAssetReplacer' => '_replaceLinks')
   * 'asset'  => array('sfSympalAssetReplacer' => '_replaceAssets')
   */
  protected
    $_replacementMap = null;

  public function __construct(sfSympalContentSlot $slot)
  {
    $this->_slot = $slot;
    $this->_content = $slot->getContentRenderedFor();
  }

  public static function listenToFilterSlotContent(sfEvent $event, $content)
  {
    $replacer = new self($event->getSubject());

    return $replacer->replace($content);
  }

  public function replace($content)
  {
    if ($parsed = $this->_parseSyntaxes($content))
    {
      $ids = $parsed['ids'];
      $replacements = $parsed['replacements'];
      
      $replacementKeys = array_keys($ids);
      // iterate through all of the replacement types
      foreach($replacementKeys as $key)
      {
        $content = $this->handleReplacementCallback($key, $ids[$key], $replacements[$key], $content);
      }
    }

    return $content;
  }
  
  /**
   * Handles the callbacks for each type of replacement and returns the
   * filtered content
   */
  protected function handleReplacementCallback($key, $ids, $replacements, $content)
  {
    $map = $this->getReplacementMap();
    $callback = $map[$key];
    
    return call_user_func($callback, $key, $ids, $replacements, $content, $this);
  }
  
  /**
   * Searches through the content and extracts out any matches. The return
   * value is a formatted array of what needs to be replaced
   */
  private function _parseSyntaxes($content)
  {
    // create the replacement string (e.g. link|asset|myObject)
    $replacementString = implode('|', array_keys($this->getReplacementMap()));
    
    preg_match_all("/\[($replacementString):(.*?)\]/", $content, $matches);

    if (isset($matches[0]) && $matches[0])
    {
      $ids = array();
      $replacements = array();

      $types = $matches[1];
      $bodies = $matches[2];

      foreach ($bodies as $key => $body)
      {
        $e = explode(' ', $body);
        $ids[$types[$key]][] = $e[0];
        $replacements[$types[$key]][] = array(
          'id' => $e[0],
          'options' => _parse_attributes(substr($body, strlen($e[0]))),
          'replace' => $matches[0][$key]
        );
      }
      return array(
        'ids' => $ids,
        'replacements' => $replacements
      );
    } else {
      return false;
    }
  }
  
  /**
   * Handles the replacement of "asset" keys
   */
  public static function _replaceAssets($key, $ids, $replacements, $content, sfSympalAssetReplacer $replacer)
  {
    if (array_diff($ids, $replacer->getContent()->Assets->getPrimaryKeys()) || array_diff($replacer->getContent()->Assets->getPrimaryKeys(), $ids))
    {
      $assetObjects = Doctrine_Core::getTable('sfSympalAsset')
        ->createQuery()
        ->from('sfSympalAsset a')
        ->whereIn('a.id', array_unique($ids))
        ->execute();
      foreach ($assetObjects as $assetObject)
      {
        $replacer->getContent()->Assets[] = $assetObject;
      }
      $replacer->getContent()->save();
    }

    $assetObjects = self::_buildObjects($replacer->getContent()->Assets);
    foreach ($replacements as $replacement)
    {
      $assetObject = $assetObjects[$replacement['id']];
      $content = $assetObject->filterContent($content, $replacement['replace'], $replacement['options']);
    }
    return $content;
  }
  
  /**
   * Handles the replacement of "link" keys
   */
  public static function _replaceLinks($key, $ids, $replacements, $content, sfSympalAssetReplacer $replacer)
  {
    if (array_diff($ids, $replacer->getContent()->Links->getPrimaryKeys()) || array_diff($replacer->getContent()->Links->getPrimaryKeys(), $ids))
    {
      $q = Doctrine_Core::getTable('sfSympalAsset')
        ->createQuery('c')
        ->from('sfSympalContent c')
        ->innerJoin('c.Type t')
        ->whereIn('c.id', array_unique($ids));

      if (sfSympalConfig::isI18nEnabled('sfSympalContent'))
      {
        $q->leftJoin('c.Translation ct');
      }

      $contentObjects = $q->execute();
      foreach ($contentObjects as $contentObject)
      {
        $replacer->getContent()->Links[] = $contentObject;
      }
      $replacer->getContent()->save();
    }

    $contentObjects = self::_buildObjects($replacer->getContent()->Links);
    foreach ($replacements as $replacement)
    {
      $contentObject = $contentObjects[$replacement['id']];
      
      $urlOnly = isset($replacement['options']['url']) ? $replacement['options']['url'] : false;
      unset($replacement['options']['url']);
      
      if ($urlOnly)
      {
        $content = str_replace($replacement['replace'], url_for($contentObject->getRoute(), $replacement['options']), $content);
      }
      else
      {
        $label = isset($replacement['options']['label']) ? $replacer->replace($replacement['options']['label']) : 'Link to content id #'.$replacement['id'];
        unset($replacement['options']['label']);
        
        $content = str_replace($replacement['replace'], link_to($label, $contentObject->getRoute(), $replacement['options']), $content);
      }
    }
    return $content;
  }
  
  /**
   * Replaces objects, we can correspond to a wide-variety of keys
   */
  public static function _replaceObjects($key, $ids, $replacements, $content, sfSympalAssetReplacer $replacer)
  {
    $slotObjectConfig = sfSympalConfig::get('content_slot_objects', $key, array());
    
    // of we can't locate the key, just replace everything with nothing
    if (!isset($slotObjectConfig['class']))
    {
      foreach($replacements as $replacement)
      {
        $content = str_replace($replacement['replace'], '', $content);
      }
      
      return $content;
    }
    
    $template = $slotObjectConfig['template'];
    $class = $slotObjectConfig['class'];
    
    // retrieve the Doctrine_Collection of objects
    $tbl = Doctrine_Core::getTable($class);
    if (method_exists($tbl, 'fetchForSlot'))
    {
      $slotObjects = $tbl->fetchForSlot($ids);
    }
    else
    {
      $slotObjects = $tbl->createQuery('s')
        ->whereIn('s.id', $ids)
        ->execute();
    }
    
    $slotObjects = self::_buildObjects($slotObjects);
    foreach ($replacements as $replacement)
    {
      $slotObject = $slotObjects[$replacement['id']];
      
      $replacement['options'][$class] = $slotObject;
      $ret = get_partial($template, $replacement['options']);
      
      $content = str_replace($replacement['replace'], $ret, $content);
    }
    
    return $content;
  }
  
  /**
   * Remaps a doctrine collection so that the keys of the colleciton "array"
   * are the ids of the underlying objects. This makes the collection
   * easier to deal with then replacing objects
   * 
   * @return Doctrine_Collection
   */
  public static function _buildObjects(Doctrine_Collection $collection)
  {
    $objects = new Doctrine_Collection($collection->getTable());
    foreach ($collection as $key => $value)
    {
      $objects[$value->id] = $value;
    }

    return $objects;
  }
  
  /**
   * Retrieves all of the replacements and their associated callbacks
   */
  protected function getReplacementMap()
  {
    if ($this->_replacementMap === null)
    {
      $map = array(
        'link'   => array('sfSympalAssetReplacer' => '_replaceLinks'),
        'asset'  => array('sfSympalAssetReplacer' => '_replaceAssets')
      );
      
      $dispatcher = sfContext::getInstance()->getConfiguration()->getEventDispatcher();
      
      $this->_replacementMap = $dispatcher->filter(
        new sfEvent($this, 'sympal.asset_replacer.filter_map'),
        $map
      )->getReturnValue();
    }
    
    return $this->_replacementMap;
  }
  
  /**
   * @return sfSympalContent
   */
  public function getContent()
  {
    return $this->_content;
  }
}