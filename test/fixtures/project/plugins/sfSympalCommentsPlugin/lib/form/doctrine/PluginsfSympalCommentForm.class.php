<?php

/**
 * PluginsfSympalComment form.
 *
 * @package    form
 * @subpackage sfSympalComment
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginsfSympalCommentForm extends BasesfSympalCommentForm
{
  public function setup()
  {
    parent::setup();
    unset($this['created_at'], $this['updated_at'], $this['content_list']);
  }
}