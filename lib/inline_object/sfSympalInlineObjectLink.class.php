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
class sfSympalInlineObjectLink extends sfInlineObjectDoctrineType
{

  /**
   * @see InlineObjectType
   */
  public function render()
  {
    $content = $this->getRelatedObject();

    if (!$content)
    {
      return '';
    }

    // Try to get a somewhat clean array of options to pass to the url or link tag
    $options = $this->getOptions();
    unset(
      $options['url'],
      $options['label']
    );

    $urlOnly = $this->getOption('url', false);
    if ($urlOnly)
    {
      return url_for($content->getRoute(), $options);
    }
    else
    {
      $label = $this->getOption('label', $content->page_title);
      
      return link_to($label, $content->getRoute(), $options);
    }
  }

  /**
   * @see sfInlineObjectDoctrineType
   */
  public function getModel()
  {
    return 'sfSympalContent';
  }

  /**
   * @see sfInlineObjectDoctrineType
   */
  public function getKeyColumn()
  {
    return 'slug';
  }

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
}