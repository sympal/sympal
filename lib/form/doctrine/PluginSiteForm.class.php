<?php

/**
 * PluginSite form.
 *
 * @package    form
 * @subpackage Site
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginSiteForm extends BaseSiteForm
{
  public function setup()
  {
    parent::setup();

    sfSympalFormToolkit::changeThemeWidget($this);
  }
}