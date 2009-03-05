<?php

/**
 * PluginEntityTemplate form.
 *
 * @package    form
 * @subpackage EntityTemplate
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginEntityTemplateForm extends BaseEntityTemplateForm
{
  public function setup()
  {
    parent::setup();
    $this->widgetSchema['body']->setAttribute('cols', 100);
    $this->widgetSchema['body']->setAttribute('rows', 20);
  }
}