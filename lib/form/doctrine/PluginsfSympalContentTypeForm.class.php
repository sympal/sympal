<?php

/**
 * PluginContentType form.
 *
 * @package    form
 * @subpackage sfSympalContentType
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginsfSympalContentTypeForm extends BasesfSympalContentTypeForm
{
  public function setup()
  {
    parent::setup();

    $plugins = sfSympalContext::getInstance()->getSympalConfiguration()->getContentTypePlugins();
    $plugins = array_merge(array('' => ''), $plugins);

    $this->widgetSchema['plugin_name'] = new sfWidgetFormChoice(array('choices' => $plugins));
    $this->widgetSchema['plugin_name']->setLabel('Plugin');

    $this->widgetSchema['name']->setLabel('Model name');

    $models = Doctrine_Core::loadModels(sfConfig::get('sf_lib_dir').'/model/doctrine');

    foreach ($models as $model)
    {
      $table = Doctrine_Core::getTable($model);
      if (!$table->hasTemplate('sfSympalContentTypeTemplate'))
      {
        unset($models[$model]);
      }
    }

    $models = array_merge(array('' => ''), $models);
    $this->widgetSchema['name'] = new sfWidgetFormChoice(array('choices' => $models));
  }
}