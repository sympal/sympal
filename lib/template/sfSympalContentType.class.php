<?php

class sfSympalContentType extends sfSympalContent
{
  public function setTableDefinition()
  {
    $this->hasColumn('content_id', 'integer');
  }

  public function setUp()
  {
    $this->hasOne('Content', array(
      'local' => 'content_id',
      'foreign' => 'id',
      'onDelete' => 'CASCADE'
    ));

    $contentTable = Doctrine::getTable('Content');
    $name = $this->getInvoker()->getTable()->getOption('name');
    $contentTable->bind(array($name, array('local' => 'id', 'foreign' => 'content_id')), Doctrine_Relation::ONE);
  }
}