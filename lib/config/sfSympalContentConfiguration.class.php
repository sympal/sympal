<?php

/**
 * Acts as an extension of sfSympalConfiguration
 * 
 * @package     sfSympalCMFPlugin
 * @subpackage  config
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */
class sfSympalContentConfiguration extends sfSympalExtendClass
{

  /**
   * Get array of configured content templates for a given moel name
   *
   * @param string $model
   * @return array $contentTemplates
   */
  public function getContentTemplates($model)
  {
    return sfSympalConfig::getDeep('content_types', $model, 'content_templates', array());
  }
}