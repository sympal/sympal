<?php

class sfSympalAdminGenSearchForm extends sfFormFilterDoctrine
{
  private $_modelName;

  protected function addSearchColumnQuery(Doctrine_Query $query, $field, $values)
  {
    $where = array();
    $params = array();

    $table = Doctrine_Core::getTable($this->_modelName);
    $columns = $table->getColumns();
    foreach ($columns as $name => $column)
    {
      if (isset($column['primary']) || $column['type'] != 'string') {
        continue;
      }
      $operator = strpos($values, '*') !== false ? 'LIKE' : '=';
      $where[] = $name.' '.$operator.' ?';
      $params[] = str_replace('*', '%', $values);
    }
    
    $query->andWhere('('.implode(' OR ', $where).')', $params);
  }

  public function setModelName($modelName)
  {
    $this->_modelName = $modelName;
  }

  public function getModelName()
  {
    return $this->_modelName;
  }

  public function setup()
  {
    $this->widgetSchema['search'] = new sfWidgetFormInputText();
    $this->validatorSchema['search'] = new sfValidatorString(array('required' => false));

    $this->widgetSchema->setHelp('search', 'Enter keywords to search by. Wildcards (*) are supported.');

    $this->widgetSchema->setNameFormat('search[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getFields()
  {
    return array(
      'search' => 'Text'
    );
  }
}
