<?php

/**
 * PluginsfSympalRedirect form.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: sfDoctrineFormPluginTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
abstract class PluginsfSympalRedirectForm extends BasesfSympalRedirectForm
{
  public function setup()
  {
    parent::setup();
    $this->useFields(array('source', 'destination'));
  }
}