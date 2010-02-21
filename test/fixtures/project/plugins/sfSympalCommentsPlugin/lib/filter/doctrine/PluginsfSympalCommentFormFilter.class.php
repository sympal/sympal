<?php

/**
 * PluginsfSympalComment form.
 *
 * @package    filters
 * @subpackage sfSympalComment *
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginsfSympalCommentFormFilter extends BasesfSympalCommentFormFilter
{
  public function setup()
  {
    parent::setup();
    
    $this->useFields(array(
      'status',
      'user_id',
      'name',
      'email_address',
      'website',
      'content_list',
    ));
  }
}