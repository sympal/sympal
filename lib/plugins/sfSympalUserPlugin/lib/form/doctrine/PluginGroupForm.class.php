<?php

/**
 * PluginGroup form.
 *
 * @package    form
 * @subpackage Group
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
abstract class PluginGroupForm extends BaseGroupForm
{
  public function setup()
  {
    parent::setup();
    unset(
      $this['created_at'],
      $this['updated_at']
    );
  }
}