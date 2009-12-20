<?php

/**
 * PluginsfSympalContentList form.
 *
 * @package    form
 * @subpackage sfSympalContentList
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
abstract class PluginsfSympalContentListForm extends BasesfSympalContentListForm
{
  public function setup()
  {
    parent::setup();

    $choices = array('' => '');

    $table = Doctrine_Core::getTable('sfSympalContent');
    foreach ($table->getColumns() as $name => $column)
    {
      $choices['Content']['c.'.$name] = $name;
    }

    $type = $this->object->ContentType->name;
    $type = $type ? $type : 'sfSympalPage';
    $groupName = str_replace('sfSympal', null, $type);

    $table = Doctrine_Core::getTable($type);
    foreach ($table->getColumns() as $name => $column)
    {
      $choices[$groupName]['cr.'.$name] = $name;
    }

    $this->widgetSchema['sort_column'] = new sfWidgetFormChoice(array('choices' => $choices));
    $this->widgetSchema['sort_order'] = new sfWidgetFormChoice(array('choices' => array('' => '', 'ASC' => 'ASC', 'DESC' => 'DESC')));
  }
}