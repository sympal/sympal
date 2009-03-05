<?php

/**
 * PluginEntityType form.
 *
 * @package    form
 * @subpackage EntityType
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginEntityTypeForm extends BaseEntityTypeForm
{
  public function setup()
  {
    parent::setup();

    sfSympalTools::changeLayoutWidget($this);
    sfSympalTools::embedI18n('entity_types', $this);
  }
}