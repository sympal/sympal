<?php

/**
 * PluginContentTemplate form.
 *
 * @package    form
 * @subpackage ContentTemplate
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginContentTemplateForm extends BaseContentTemplateForm
{
  public function setup()
  {
    parent::setup();
    $this->widgetSchema['body']->setAttribute('cols', 100);
    $this->widgetSchema['body']->setAttribute('rows', 20);
  }
}