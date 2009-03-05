<?php

/**
 * PluginEntity form.
 *
 * @package    filters
 * @subpackage Entity *
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginEntityFormFilter extends BaseEntityFormFilter
{
  public function setup()
  {
    parent::setup();
    unset($this['custom_path'], $this['layout'], $this['slug'], $this['created_at'], $this['updated_at']);
  }
}