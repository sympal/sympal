<?php

/**
 * PluginSite form.
 *
 * @package    form
 * @subpackage sfSympalSite
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginsfSympalSiteForm extends BasesfSympalSiteForm
{
  public function setup()
  {
    parent::setup();

    // Don't allow editing of slug if we're dealing with an already existing site/application
    if (!$this->isNew())
    {
      unset($this['slug']);
    }
  }
}