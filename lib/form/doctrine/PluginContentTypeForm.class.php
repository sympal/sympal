<?php

/**
 * PluginContentType form.
 *
 * @package    form
 * @subpackage sfSympalContentType
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginsfSympalContentTypeForm extends BasesfSympalContentTypeForm
{
  public function setup()
  {
    parent::setup();

    sfSympalFormToolkit::changeThemeWidget($this);
    unset($this['name']);
  }
}