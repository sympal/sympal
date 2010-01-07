<?php

class sfSympalContentFilter extends Doctrine_Record_Filter
{
  public function filterSet(Doctrine_Record $record, $name, $value)
  {
    try {
      if ($record->getRecord())
      {
        $record->getRecord()->$name = $value;
        return $record;
      }
    } catch (Exception $e) {}

    throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
  }

  public function filterGet(Doctrine_Record $record, $name)
  {
    try {
      if ($record->getRecord())
      {
        return $record->getRecord()->$name;
      }
    } catch (Exception $e) {}

    throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
  }
}