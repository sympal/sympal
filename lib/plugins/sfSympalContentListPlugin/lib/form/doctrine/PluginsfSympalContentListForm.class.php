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

    $tableMethods = $this->_getTableMethods(array($type.'Table', 'Plugin'.$type.'Table'));

    $this->widgetSchema['table_method'] = new sfWidgetFormChoice(array('choices' => $tableMethods));
  }

  private function _getTableMethods($classes)
  {
    if (is_array($classes))
    {
      $tableMethods = array();
      foreach ($classes as $class)
      {
        $tableMethods = array_merge($tableMethods, $this->_getTableMethods($class));
      }
      return $tableMethods;
    } else {
      $class = $classes;
      $array1 = get_class_methods($class);
      if ($parentClass = get_parent_class($class)){
        $array2 = get_class_methods($parentClass);
        $array3 = array_diff($array1, $array2);
      } else {
        $array3 = $array1;
      }
      return $array3;
    }
  }
}