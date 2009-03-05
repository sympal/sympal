<?php

/**
 * PluginComment form.
 *
 * @package    filters
 * @subpackage Comment *
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginCommentFormFilter extends BaseCommentFormFilter
{
  public function setup()
  {
    parent::setup();
    unset($this['created_at'], $this['updated_at'], $this['body']);
  }
}