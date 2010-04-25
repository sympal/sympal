<?php

/**
 * Class responsible for actually processing the asset syntaxes:
 * 
 * [asset:my_file.gif]
 * [asset:my_file.gif alt="my cool image"]
 * 
 * The heavy-lifting is done elsewhere.
 * 
 * @package     sfSympalAssetsPlugin
 * @subpackage  inline_object
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-01-30
 * @version     svn:$Id$ $Author$
 */
class sfSympalInlineObjectAsset extends sfInlineObjectDoctrineType
{
  /**
   * @see InlineObjectType
   */
  public function render()
  {
    $asset = $this->getRelatedObject();

    if (!$asset)
    {
      return '';
    }

    return $asset->render($this->getOptions());
  }

  /**
   * @see sfInlineObjectDoctrineType
   */
  public function getModel()
  {
    return 'sfSympalAsset';
  }

  /**
   * @see sfInlineObjectDoctrineType
   */
  public function getKeyColumn()
  {
    return 'slug';
  }
}