<?php

class sfSympalEntityType extends Doctrine_Template
{
  public function setTableDefinition()
  {
    $this->hasColumn('entity_id', 'integer', 4);
  }

  public function setUp()
  {
    $this->hasOne('Entity', array(
      'local' => 'entity_id',
      'foreign' => 'id',
      'onDelete' => 'CASCADE'
    ));

    $entityTable = Doctrine::getTable('Entity');
    $name = $this->getInvoker()->getTable()->getOption('name');
    $entityTable->bind(array($name, array('local' => 'id', 'foreign' => 'entity_id')), Doctrine_Relation::ONE);
  }
}