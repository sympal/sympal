<?php
class sfSympalDoctrineRecordFilter extends Doctrine_Record_Filter
{
  public function init()
  {
  }

  public function filterSet(Doctrine_Record $record, $name, $value)
  {
    $table = $record->getTable();
    if ($table->hasRelation('Entity'))
    {
      return $record['Entity'][$name] = $value;
    } else {
      return false;
    }
  }

  public function filterGet(Doctrine_Record $record, $name)
  {
    $table = $record->getTable();
    if ($table->hasRelation('Entity'))
    {
      return $record['Entity'][$name];
    } else {
      return false;
    }
  }
}