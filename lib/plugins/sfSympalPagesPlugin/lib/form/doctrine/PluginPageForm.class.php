<?php

/**
 * PluginPage form.
 *
 * @package    form
 * @subpackage Page
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginPageForm extends BasePageForm
{
  public function setup()
  {
    parent::setup();
    unset($this['updated_at'], $this['created_at'], $this['entity_id'], $this['comments_list']);
  }
}