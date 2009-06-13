<?php

/**
 * PluginContentType form.
 *
 * @package    form
 * @subpackage ContentType
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginContentTypeForm extends BaseContentTypeForm
{
  public function setup()
  {
    parent::setup();

    sfSympalFormToolkit::changeThemeWidget($this);

    $this->widgetSchema['schema'] = new sfWidgetFormTextarea(array(), array('style' => 'width: 600px; height: 400px;'));
    $this->validatorSchema['schema'] = new sfValidatorString(array('required' => true));
    $this->validatorSchema['plugin_name']->setOption('required', true);
    $this->widgetSchema['name']->setLabel('Model Name');
  }

  public function updateObject($values = null)
  {
    if (is_null($values))
    {
      $values = $this->values;
    }

    $this->object->schema = $values['schema'];

    return parent::updateObject($values);
  }
}