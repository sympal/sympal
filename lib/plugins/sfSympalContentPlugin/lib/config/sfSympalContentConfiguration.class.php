<?php

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
    return sfSympalConfig::get($model, 'content_templates', array());
  }
}