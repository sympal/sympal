<?php

/**
 * PluginPermission form.
 *
 * @package    form
 * @subpackage Permission
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
abstract class PluginPermissionForm extends BasePermissionForm
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