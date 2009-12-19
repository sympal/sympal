<?php

/**
 * PluginsfSympalPage form.
 *
 * @package    form
 * @subpackage sfSympalPage
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginsfSympalPageForm extends BasesfSympalPageForm
{
  public function setup()
  {
    parent::setup();
    unset($this['updated_at'], $this['created_at'], $this['content_id'], $this['comments_list']);
  }
}